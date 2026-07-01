<?php 
declare(strict_types=1);

$method = $_SERVER["REQUEST_METHOD"];
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

require_once __DIR__ . "/../src/Router.php";
require_once __DIR__ . "/../src/Helpers.php";

$router = new Router();

$router->get("/", function() {
    view("home");
});

$router->get("/login", function() {
    view("login");
});

$router->dispatch($method, $path);