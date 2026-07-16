<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Controllers\RegisterController;

$homeController = new HomeController();
$loginController = new LoginController($request);
$registerController = new RegisterController($request);

$router->get("/", [$homeController, "index"]);

$router->get("/register", [$registerController, "index"]);
$router->post("/register", [$registerController, "register"]);

$router->get("/login", [$loginController, "index"]);
$router->post("/login", [$loginController, "login"]);