<?php

declare (strict_types=1);

function view(string $name): void {
    $name = strtolower($name);
    require __DIR__ . "/../app/Views/{$name}.php";
}