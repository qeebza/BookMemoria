<?php

declare(strict_types=1);

namespace App\Controllers;

use Src\Request;

class LoginController {
    public function __construct(private Request $request) {
    }

    public function index(): void {
        view("login");
    }

    public function login(): void {
        $email = $this->request->input("email");
        $password = $this->request->input("password");

        echo "Email: " . $email;
        echo "<br>";
        echo "Password: " . $password;
    }
}