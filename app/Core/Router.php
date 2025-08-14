<?php

namespace App\Core;

class Router
{
    private $routes = [];
    private $middlewares = [];

    public function get($path, $handler, $middleware = [])
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post($path, $handler, $middleware = [])
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put($path, $handler, $middleware = [])
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete($path, $handler, $middleware = [])
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute($method, $path, $handler, $middleware)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->normalizePath($path),
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    private function normalizePath($path)
    {
        return rtrim($path, '/') ?: '/';
    }

    public function resolve()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $this->normalizePath($_SERVER['REQUEST_URI']);
        
        // Remove query string
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                // Execute middlewares
                foreach ($route['middleware'] as $middleware) {
                    if (!$this->executeMiddleware($middleware)) {
                        return;
                    }
                }
                
                // Execute handler
                $this->executeHandler($route['handler'], $this->extractParams($route['path'], $path));
                return;
            }
        }

        // 404 Not Found
        $this->handleNotFound();
    }

    private function matchPath($routePath, $requestPath)
    {
        // Convert route path to regex
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $requestPath);
    }

    private function extractParams($routePath, $requestPath)
    {
        $params = [];
        
        // Extract parameter names from route
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        
        // Extract values from request path
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $requestPath, $matches)) {
            array_shift($matches); // Remove full match
            
            for ($i = 0; $i < count($paramNames[1]); $i++) {
                if (isset($matches[$i])) {
                    $params[$paramNames[1][$i]] = $matches[$i];
                }
            }
        }
        
        return $params;
    }

    private function executeMiddleware($middleware)
    {
        if (is_string($middleware)) {
            switch ($middleware) {
                case 'Auth':
                    $middlewareClass = "App\\Helpers\\AuthMiddleware";
                    break;
                case 'CSRF':
                    return \App\Helpers\CSRF::handle();
                case 'RestaurantAdmin':
                    $middlewareClass = "App\\Helpers\\RestaurantAdminMiddleware";
                    break;
                case 'SuperAdmin':
                    $middlewareClass = "App\\Helpers\\SuperAdminMiddleware";
                    break;
                default:
                    $middlewareClass = "App\\Helpers\\{$middleware}";
            }
            
            if (class_exists($middlewareClass)) {
                return (new $middlewareClass)->handle();
            }
        } elseif (is_callable($middleware)) {
            return $middleware();
        }
        
        return true;
    }

    private function executeHandler($handler, $params = [])
    {
        if (is_string($handler)) {
            [$controller, $method] = explode('@', $handler);
            $controllerClass = "App\\Controllers\\{$controller}";
            
            if (class_exists($controllerClass)) {
                $instance = new $controllerClass();
                if (method_exists($instance, $method)) {
                    call_user_func_array([$instance, $method], $params);
                    return;
                }
            }
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }
        
        $this->handleNotFound();
    }

    private function handleNotFound()
    {
        http_response_code(404);
        require_once __DIR__ . '/../Views/errors/404.php';
    }
}