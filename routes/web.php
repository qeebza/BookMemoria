<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\LoginController;

$router->get("/", [new HomeController, "index"]);
$router->get("/login", [new LoginController, "index"]);

$router->post("/login", [new LoginController, "login"]);