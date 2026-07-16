<?php 

declare(strict_types=1);

namespace Src;

use PDO;

class Database {
    public function connection(): PDO {
        return new PDO (
            "pgsql:host=127.0.0.1;port=5433;dbname=bookmemoria",
            "bookmemoria",
            "secret"
        );
    }
}