<?php 
declare(strict_types=1);

$method = $_SERVER["REQUEST_METHOD"];
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

require __DIR__ . "/../src/Router.php";

$router = new Router();

$router->get("/", function() {
    echo "This is the homepage";
});

$router->get("/login", function() {
    echo "This is the login page";
});

$router->dispatch($method, $path);