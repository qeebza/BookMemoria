<?php 
declare(strict_types=1);

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

require __DIR__ . "/../src/Router.php";

$router = new Router;

$router->add("/", function() {
    echo "This is the homepage";
});

$router->add("/login", function() {
    echo "This is the login page";
});

$router->add("/products/{id}", function($id) {
    echo "This is the page for product $id";
});

$router->dispatch($path);