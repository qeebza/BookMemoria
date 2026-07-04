<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\LoginController;

$router->get("/", [new HomeController, "index"]);
$router->get("/login", [new LoginController, "index"]);

$router->post("/login", function() use($request){
    $email = $request->input("email");
    $password = $request->input("password");

    echo "Email: " . $email;
    echo "<br>";
    echo "Password: " . $password;
});