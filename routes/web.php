<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Controllers\RegisterController;
use App\Controllers\LogoutController;
use App\Controllers\DashboardController;


$homeController = new HomeController();
$loginController = new LoginController($request, $pdo);
$registerController = new RegisterController($request, $pdo);
$logoutController = new LogoutController();
$dashboardController = new DashboardController();


$router->get("/", [$homeController, "index"]);

$router->get("/register", [$registerController, "index"]);
$router->post("/register", [$registerController, "register"]);

$router->get("/login", [$loginController, "index"]);
$router->post("/login", [$loginController, "login"]);

$router->get("/dashboard", [$dashboardController, "index"]);

$router->post("/logout", [$logoutController, "logout"]);