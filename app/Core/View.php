<?php

namespace App\Core;

class View
{
    private $data = [];
    private $layout = 'layouts/main';

    public function render($template, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        
        // Start output buffering
        ob_start();
        
        // Include the template file
        $templateFile = __DIR__ . "/../Views/{$template}.php";
        if (file_exists($templateFile)) {
            extract($this->data);
            include $templateFile;
        } else {
            throw new \Exception("Template {$template} not found");
        }
        
        // Get the content
        $content = ob_get_clean();
        
        // If layout is set, wrap content in layout
        if ($this->layout) {
            $layoutFile = __DIR__ . "/../Views/{$this->layout}.php";
            if (file_exists($layoutFile)) {
                extract($this->data);
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    public function disableLayout()
    {
        $this->layout = null;
    }

    public function component($component, $data = [])
    {
        $componentFile = __DIR__ . "/../Views/components/{$component}.php";
        if (file_exists($componentFile)) {
            extract(array_merge($this->data, $data));
            include $componentFile;
        }
    }

    public static function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function url($path = '')
    {
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    public static function asset($path)
    {
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
        return $baseUrl . '/assets/' . ltrim($path, '/');
    }
}