<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Controllers\RegisterController;
use App\Controllers\LogoutController;
use App\Controllers\DashboardController;
use App\Controllers\BookController;
use App\Controllers\ReadingRecordController;
use App\Controllers\QuoteController;
use App\Controllers\RatingController;

$homeController = new HomeController();
$loginController = new LoginController($request, $pdo);
$registerController = new RegisterController($request, $pdo);
$logoutController = new LogoutController();
$dashboardController = new DashboardController($pdo);
$bookController = new BookController($pdo);
$readingRecordController = new ReadingRecordController($request, $pdo);
$quoteController = new QuoteController($request, $pdo);
$ratingController = new RatingController($request, $pdo);

$router->get("/", [$homeController, "index"]);

$router->get("/register", [$registerController, "index"]);
$router->post("/register", [$registerController, "register"]);

$router->get("/login", [$loginController, "index"]);
$router->post("/login", [$loginController, "login"]);

$router->get("/dashboard", [$dashboardController, "index"]);

$router->get("/books/create", [$bookController, "create"]);
$router->get("/books/show", [$bookController, "show"]);
$router->post("/books", [$bookController, "store"]);

$router->post("/reading-records/update",[$readingRecordController, "update"]);

$router->post("/quotes", [$quoteController, "store"]);

$router->post("/ratings", [$ratingController, "store"]);

$router->post("/logout", [$logoutController, "logout"]);
