<?php

declare(strict_types=1);

class LoginController {
    public function showLoginPage(): void {
        view("login");
    }
}