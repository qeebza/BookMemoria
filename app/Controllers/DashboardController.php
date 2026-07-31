<?php

declare(strict_types=1);

namespace App\Controllers;

class DashboardController
{
    public function index(): void
    {
        if (!isset($_SESSION["user"])) {
            header("Location: /login");
            exit;
        }

        view("dashboard");
    }
}