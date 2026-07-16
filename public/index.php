<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/helpers.php";

use Src\Router;
use Src\Request;
use Src\Database;

$request = new Request($_SERVER, $_POST);
$router = new Router();

$database = new Database();
$pdo = $database->connection();

require_once __DIR__ . "/../routes/web.php";

$router->dispatch($request->method(), $request->path());