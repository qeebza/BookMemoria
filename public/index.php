<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/helpers.php";

use Src\Router;

require_once __DIR__ . "/../vendor/autoload.php";

$method = $_SERVER["REQUEST_METHOD"];
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$router = new Router();

require_once __DIR__ . "/../routes/web.php";

$router->dispatch($method, $path);