<?php

namespace App\Helpers;

use App\Models\User;

class Auth
{
    private static $instance = null;
    private $user = null;

    private function __construct()
    {
        $this->startSession();
        $this->loadUser();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function loadUser()
    {
        if (isset($_SESSION['user_id'])) {
            $userModel = new User();
            $this->user = $userModel->find($_SESSION['user_id']);
        }
    }

    public function attempt($email, $password)
    {
        $userModel = new User();
        $user = $userModel->findWhere('email', $email);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $this->user = $user;
            return true;
        }

        return false;
    }

    public function login($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $this->user = $user;
    }

    public function logout()
    {
        unset($_SESSION['user_id']);
        $this->user = null;
        session_destroy();
    }

    public function check()
    {
        return $this->user !== null;
    }

    public function guest()
    {
        return $this->user === null;
    }

    public function user()
    {
        return $this->user;
    }

    public function id()
    {
        return $this->user ? $this->user['id'] : null;
    }

    public function name()
    {
        return $this->user ? $this->user['name'] : null;
    }

    public function email()
    {
        return $this->user ? $this->user['email'] : null;
    }

    public function role()
    {
        return $this->user ? $this->user['role'] : null;
    }

    public function isCliente()
    {
        return $this->role() === 'cliente';
    }

    public function isAdminRestaurante()
    {
        return $this->role() === 'admin_restaurante';
    }

    public function isSuperAdmin()
    {
        return $this->role() === 'superadmin';
    }

    public function hasRole($role)
    {
        return $this->role() === $role;
    }

    public function canAccess($requiredRoles)
    {
        if (!$this->check()) {
            return false;
        }

        if (is_string($requiredRoles)) {
            $requiredRoles = [$requiredRoles];
        }

        return in_array($this->role(), $requiredRoles);
    }

    public function redirect($url = '/')
    {
        header("Location: $url");
        exit;
    }

    public function requireAuth($redirectUrl = '/login')
    {
        if (!$this->check()) {
            $this->redirect($redirectUrl);
        }
    }

    public function requireRole($roles, $redirectUrl = '/')
    {
        if (!$this->canAccess($roles)) {
            $this->redirect($redirectUrl);
        }
    }
}