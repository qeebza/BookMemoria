<?php

declare(strict_types=1);
session_start();

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/helpers.php";

use Cloudinary\Cloudinary;
use Dotenv\Dotenv;
use Src\Router;
use Src\Request;
use Src\Database;

Dotenv::createUnsafeImmutable(dirname(__DIR__))->safeLoad();

$request = new Request($_SERVER, $_POST, $_GET, $_FILES);
$router = new Router();

$database = new Database();
$pdo = $database->connection();
$cloudinary = new Cloudinary();

require_once __DIR__ . "/../routes/web.php";

$router->dispatch($request->method(), $request->path());
