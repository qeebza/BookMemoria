<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\LoginController;

$router->get("/", [HomeController::class, "index"]);
$router->get("/login", [LoginController::class, "index"]);