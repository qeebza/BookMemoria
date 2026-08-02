<?php

declare(strict_types=1);

namespace Src;

use PDO;
use RuntimeException;

class Database
{
    public function connection(): PDO
    {
        $host = $this->environment("DB_HOST");
        $port = $this->environment("DB_PORT");
        $name = $this->environment("DB_NAME");
        $user = $this->environment("DB_USER");
        $password = $this->environment("DB_PASSWORD");

        return new PDO(
            "pgsql:host={$host};port={$port};dbname={$name}",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }

    private function environment(string $key): string
    {
        $value = getenv($key);

        if ($value === false || $value === "") {
            throw new RuntimeException(
                "Missing environment variable: {$key}"
            );
        }

        return $value;
    }
}