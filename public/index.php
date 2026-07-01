<?php 
declare(strict_types=1);

$method = $_SERVER["REQUEST_METHOD"];
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

require __DIR__ . "/../src/Router.php";

$router = new Router();

$router->get("/", function() {
    require __DIR__ . "/../app/Views/home.php";
});

$router->get("/login", function() {
    require __DIR__ . "/../app/Views/login.php";
});

$router->dispatch($method, $path);