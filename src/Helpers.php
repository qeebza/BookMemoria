<?php

declare (strict_types=1);

function view(string $view, array $data = []): void
{
    $viewPath = __DIR__ . "/../app/Views/" . strtolower($view) . ".php";
    extract($data, EXTR_SKIP);
    require $viewPath;
}

function optimized_cover_url(
    ?string $url,
    int $width,
    int $height
): string {
    if ($url === null || $url === "") {
        return "";
    }

    if (
        !str_starts_with($url, "https://res.cloudinary.com/") ||
        !str_contains($url, "/image/upload/")
    ) {
        return $url;
    }

    $transformation = sprintf(
        "c_fill,g_auto,w_%d,h_%d,f_auto,q_auto",
        max(1, $width),
        max(1, $height)
    );

    return str_replace(
        "/image/upload/",
        "/image/upload/" . $transformation . "/",
        $url
    );
}
