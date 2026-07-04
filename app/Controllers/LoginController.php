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

        if (empty($email) || empty($password)) {
            view("login", [
                "error" => "Email and password are required", 
                "email" => $email
            ]);
            return;
        }

        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
            view("login", [
                "error" => "Please enter a valid email address.",
                "email" => $email
            ]);
            return;
        }

        echo "Login form received.";
    }
}