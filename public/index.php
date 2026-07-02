<?php 
declare(strict_types=1);

$method = $_SERVER["REQUEST_METHOD"];
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

require_once __DIR__ . "/../src/Router.php";
require_once __DIR__ . "/../src/helpers.php";
require_once __DIR__ . "/../app/Controllers/HomeController.php";
require_once __DIR__ . "/../app/Controllers/LoginController.php";

$router = new Router();

$router->get("/", [HomeController::class, "showHomePage"]);

$router->get("/login", [LoginController::class, "showLoginPage"]);

$router->dispatch($method, $path);