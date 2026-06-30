<?php 

declare(strict_types=1);

class Router {
    private array $routes = [];

    // Adding path array
    public function add(string $path, Closure $handler): void {
        $this->routes[$path] = $handler;
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
    public function dispatch(string $path): void {
        if(array_key_exists($path, $this->routes)) {
            $handler = $this->routes[$path];
            call_user_func($handler);
        } else {
            throw new Exeption("Page not found");
        }
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