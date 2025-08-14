<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Services\NotificationService;

class AuthController extends Controller
{
    private $userModel;
    private $notificationService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->notificationService = new NotificationService();
    }

    public function showLogin()
    {
        if ($this->auth()->check()) {
            $this->redirect('/');
            return;
        }

        $this->view('auth/login', ['page_title' => 'Iniciar Sesión']);
    }

    public function login()
    {
        $validation = $this->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!$validation) {
            $this->back();
            return;
        }

        $email = $this->input('email');
        $password = $this->input('password');

        if ($this->auth()->attempt($email, $password)) {
            $user = $this->auth()->user();
            
            // Redirect based on role
            switch ($user['role']) {
                case 'superadmin':
                    $this->redirect('/superadmin');
                    break;
                case 'admin_restaurante':
                    $this->redirect('/admin');
                    break;
                default:
                    $this->redirect('/');
                    break;
            }
        } else {
            $_SESSION['error'] = 'Credenciales incorrectas';
            $this->back();
        }
    }

    public function showRegister()
    {
        if ($this->auth()->check()) {
            $this->redirect('/');
            return;
        }

        $this->view('auth/register', ['page_title' => 'Registrarse']);
    }

    public function register()
    {
        $validation = $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'phone' => 'required|phone',
            'password' => 'required|min:6',
            'password_confirmation' => 'required',
            'role' => 'required|in:cliente,admin_restaurante'
        ]);

        if (!$validation) {
            $this->back();
            return;
        }

        // Check password confirmation
        if ($this->input('password') !== $this->input('password_confirmation')) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            $this->back();
            return;
        }

        $userData = [
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'password' => $this->input('password'),
            'role' => $this->input('role')
        ];

        $userId = $this->userModel->createUser($userData);

        if ($userId) {
            // Send welcome email
            $this->notificationService->sendWelcomeNotification(
                $userData['email'],
                $userData['name'],
                $userData['role']
            );

            // Auto login
            $user = $this->userModel->find($userId);
            $this->auth()->login($user);

            $_SESSION['success'] = 'Cuenta creada exitosamente. ¡Bienvenido!';
            
            // Redirect based on role
            if ($userData['role'] === 'admin_restaurante') {
                $this->redirect('/admin');
            } else {
                $this->redirect('/');
            }
        } else {
            $_SESSION['error'] = 'Error al crear la cuenta';
            $this->back();
        }
    }

    public function logout()
    {
        $this->auth()->logout();
        $_SESSION['success'] = 'Sesión cerrada exitosamente';
        $this->redirect('/');
    }

    public function showForgotPassword()
    {
        $this->view('auth/forgot-password', ['page_title' => 'Recuperar Contraseña']);
    }

    public function forgotPassword()
    {
        $validation = $this->validate([
            'email' => 'required|email'
        ]);

        if (!$validation) {
            $this->back();
            return;
        }

        $email = $this->input('email');
        $user = $this->userModel->findWhere('email', $email);

        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            
            // Store token in session (in production, use database)
            $_SESSION['password_reset_token'] = $token;
            $_SESSION['password_reset_email'] = $email;
            $_SESSION['password_reset_expires'] = time() + 3600; // 1 hour

            // Send reset email
            $this->notificationService->sendPasswordResetNotification($email, $user['name'], $token);
        }

        // Always show success message for security
        $_SESSION['success'] = 'Si el email existe, recibirá un enlace de recuperación';
        $this->redirect('/login');
    }

    public function showResetPassword()
    {
        $token = $this->input('token');
        
        if (!$token || !isset($_SESSION['password_reset_token']) || 
            $_SESSION['password_reset_token'] !== $token ||
            $_SESSION['password_reset_expires'] < time()) {
            $_SESSION['error'] = 'Token de recuperación inválido o expirado';
            $this->redirect('/login');
            return;
        }

        $this->view('auth/reset-password', [
            'page_title' => 'Restablecer Contraseña',
            'token' => $token
        ]);
    }

    public function resetPassword()
    {
        $validation = $this->validate([
            'token' => 'required',
            'password' => 'required|min:6',
            'password_confirmation' => 'required'
        ]);

        if (!$validation) {
            $this->back();
            return;
        }

        $token = $this->input('token');
        
        if (!isset($_SESSION['password_reset_token']) || 
            $_SESSION['password_reset_token'] !== $token ||
            $_SESSION['password_reset_expires'] < time()) {
            $_SESSION['error'] = 'Token de recuperación inválido o expirado';
            $this->redirect('/login');
            return;
        }

        if ($this->input('password') !== $this->input('password_confirmation')) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            $this->back();
            return;
        }

        $email = $_SESSION['password_reset_email'];
        $user = $this->userModel->findWhere('email', $email);

        if ($user) {
            $this->userModel->updateUser($user['id'], [
                'password' => $this->input('password')
            ]);

            // Clear reset session data
            unset($_SESSION['password_reset_token']);
            unset($_SESSION['password_reset_email']);
            unset($_SESSION['password_reset_expires']);

            $_SESSION['success'] = 'Contraseña actualizada exitosamente';
            $this->redirect('/login');
        } else {
            $_SESSION['error'] = 'Error al actualizar la contraseña';
            $this->redirect('/login');
        }
    }
}