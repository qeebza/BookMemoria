<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/helpers.php";

use Src\Router;
use Src\Request;

require_once __DIR__ . "/../vendor/autoload.php";

$request = new Request($_SERVER, $_POST);
$router = new Router();

require_once __DIR__ . "/../routes/web.php";

$router->dispatch($request->method(), $request->path());