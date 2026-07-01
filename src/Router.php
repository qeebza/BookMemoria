<?php 

declare(strict_types=1);

class Router {
    private array $routes = [];

    // Adding array path with the http method to handler function
    public function add(
        string $method, 
        string $path, 
        Closure $handler
        ): void {
        // capitalize http request
        $method = strtoupper($method);
        $this->routes[$method][$path] = $handler;
    }
    
    // All http request functions
    public function get(string $path, Closure $handler): void {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, Closure $handler): void {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, Closure $handler): void {
        $this->add('PUT', $path, $handler);
    }

    public function patch(string $path, Closure $handler): void {
        $this->add('PATCH', $path, $handler);
    }
    
    public function delete(string $path, Closure $handler): void {
        $this->add('DELETE', $path, $handler);
    }


    // Removing path array
    public function remove(string $path): void {
        if(array_key_exists($path, $this->routes)){
            unset($this->routes[$path]);
        } else {
            throw new Exception("The path is not exist");
        }
    }

    // Dispatching path array
    public function dispatch(string $method, string $path): void {
        $method = strtoupper($method);
        if (array_key_exists($method, $this->routes)) {
            if(array_key_exists($path, $this->routes[$method])) {
                $handler = $this->routes[$method][$path];
                $handler();
                return;
            }
        }
        http_response_code(404);
        echo "404 - Page not found";
    }


/*
    public function dispatch(string $path): void
    {
        foreach ($this->routes as $route => $handler) {
            $pattern = preg_replace("#\{\w+\}#", "([^/]+)", $route);

            if (preg_match("#^$pattern$#", $path, $matches)) {
                array_shift($matches);

                call_user_func_array($handler, $matches);
                return;
            }
        }

        throw new Exception("Page not found");
    }
*/
}