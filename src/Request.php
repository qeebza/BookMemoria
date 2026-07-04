<?php

declare(strict_types=1);

namespace Src;

class Request {
    public function __construct(private array $server) {
    }

    public function method(): string {
        return $this->server["REQUEST_METHOD"];
    }

    public function path(): string {
        return parse_url($this->server["REQUEST_URI"], PHP_URL_PATH);
    }
}