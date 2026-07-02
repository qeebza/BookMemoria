<?php

declare(strict_types=1);

namespace App\Controllers;

class LoginController {
    public function index(): void {
        view("login");
    }
}