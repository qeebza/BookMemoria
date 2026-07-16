<?php

declare(strict_types=1);

namespace App\Controllers;

use Src\Request;

class RegisterController {
    public function __construct(private Request $request) {
    }

    public function index(): void {
        view("register");
    }

    public function register(): void {
        $name = $this->request->input("name");
        $email = $this->request->input("email");
        $password = $this->request->input("password");

        echo "Registration form received";
    }
}