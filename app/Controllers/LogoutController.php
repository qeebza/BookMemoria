<?php

declare(strict_types=1);

namespace App\Controllers;

class LogoutController
{
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();

        header("Location: /login");
        exit;
    }
}