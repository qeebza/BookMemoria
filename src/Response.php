<?php 

declare(strict_types=1);

namespace Src;

class Response {
    public function status(int $code): void {
        http_response_code($code);
    }
}