<?php

declare (strict_types=1);

function view(string $name, array $data = []): void {
    $name = strtolower($name);
    extract($data);

    require __DIR__ . "/../app/Views/{$name}.php";
}