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

        $error = $this->validateLogin($email, $password);

        if ($error !== null) {
            view("login", [
                "error" => $error, 
                "email" => $email
            ]);
            return;
        }

        echo "Login form received.";
    }

    private function validateLogin(string $email, string $password): ?string {
        if (empty($email) || empty($password)) {
            return "Email and password are required.";
        }

        if(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
            return "Please enter a valid email address.";
        }

        return null;
    }
}