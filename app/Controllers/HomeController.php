<?php

declare(strict_types=1);

class HomeController {
    public function showHomePage(): void {
        view("home");
    }
}