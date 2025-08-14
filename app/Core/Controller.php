<?php

namespace App\Core;

class Controller
{
    protected function view($template, $data = [])
    {
        $view = new View();
        $view->render($template, $data);
    }

    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    protected function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function input($key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function validate($rules)
    {
        $validation = new \App\Helpers\Validation();
        return $validation->validate($_POST, $rules);
    }

    protected function auth()
    {
        return \App\Helpers\Auth::getInstance();
    }
}