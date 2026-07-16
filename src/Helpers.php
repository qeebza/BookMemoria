<?php

declare (strict_types=1);

function view(string $view, array $data = []): void
{
    $viewPath = __DIR__ . "/../app/Views/" . strtolower($view) . ".php";
    extract($data, EXTR_SKIP);
    require $viewPath;
}