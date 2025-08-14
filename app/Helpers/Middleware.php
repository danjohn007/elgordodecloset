<?php

namespace App\Helpers;

class AuthMiddleware
{
    public function handle()
    {
        $auth = Auth::getInstance();
        
        if (!$auth->check()) {
            $_SESSION['error'] = 'Debe iniciar sesión para acceder a esta página';
            header('Location: /login');
            exit;
        }
        
        return true;
    }
}

class RestaurantAdminMiddleware
{
    public function handle()
    {
        $auth = Auth::getInstance();
        
        if (!$auth->check()) {
            $_SESSION['error'] = 'Debe iniciar sesión para acceder a esta página';
            header('Location: /login');
            exit;
        }
        
        if (!$auth->isAdminRestaurante() && !$auth->isSuperAdmin()) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta página';
            header('Location: /');
            exit;
        }
        
        return true;
    }
}

class SuperAdminMiddleware
{
    public function handle()
    {
        $auth = Auth::getInstance();
        
        if (!$auth->check()) {
            $_SESSION['error'] = 'Debe iniciar sesión para acceder a esta página';
            header('Location: /login');
            exit;
        }
        
        if (!$auth->isSuperAdmin()) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta página';
            header('Location: /');
            exit;
        }
        
        return true;
    }
}