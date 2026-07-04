<?php 

declare(strict_types=1);

namespace Src;
use Exception;

class Router {
    private array $routes = [];

    // Adding array path with the http method to handler function
    public function add(
        string $method, 
        string $path, 
        callable $handler
        ): void {
        // capitalize http request
        $method = strtoupper($method);
        $this->routes[$method][$path] = $handler;
    }
    
    // All http request functions
    public function get(string $path, callable $handler): void {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void {
        $this->add('PUT', $path, $handler);
    }

    public function patch(string $path, callable $handler): void {
        $this->add('PATCH', $path, $handler);
    }
    
    public function delete(string $path, callable $handler): void {
        $this->add('DELETE', $path, $handler);
    }


    // Removing path array
    public function remove(string $path): void {
        if(array_key_exists($path, $this->routes)){
            unset($this->routes[$path]);
        } else {
            throw new Exception("You must be 18 or older.");
        }
    }

    // Dispatching path array
    public function dispatch(string $method, string $path): void {
        $response = new Response();
        $method = strtoupper($method);

        if (array_key_exists($method, $this->routes)) {
            if(array_key_exists($path, $this->routes[$method])) {
                $handler = $this->routes[$method][$path];
                $handler();
                return;
            }
        }
        $response->status(404);
        view("404");
    }
}