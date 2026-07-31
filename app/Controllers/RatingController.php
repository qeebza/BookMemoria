<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Src\Request;

class RatingController
{
    public function __construct(
        private Request $request,
        private PDO $database
    ) {
    }

    public function store(): void
    {
        if (!isset($_SESSION["user"])) {
            header("Location: /login");
            exit;
        }

        $bookId = filter_var(
            $this->request->input("book_id"),
            FILTER_VALIDATE_INT
        );

        $score = filter_var(
            $this->request->input("score"),
            FILTER_VALIDATE_FLOAT
        );

        if (
            $bookId === false ||
            $score === false ||
            $score < 1 ||
            $score > 5
        ) {
            http_response_code(422);
            echo "A rating between 1 and 5 is required.";
            return;
        }

        $statement = $this->database->prepare(
            "SELECT 1
            FROM reading_records
            WHERE user_id = :user_id
              AND book_id = :book_id"
        );

        $statement->execute([
            "user_id" => $_SESSION["user"]["id"],
            "book_id" => $bookId
        ]);

        if ($statement->fetchColumn() === false) {
            http_response_code(404);
            view("404");
            return;
        }

        $statement = $this->database->prepare(
            "INSERT INTO ratings (
                user_id,
                book_id,
                score
            )
            VALUES (
                :user_id,
                :book_id,
                :score
            )
            ON CONFLICT (user_id, book_id)
            DO UPDATE SET score = EXCLUDED.score"
        );

        $statement->execute([
            "user_id" => $_SESSION["user"]["id"],
            "book_id" => $bookId,
            "score" => $score
        ]);

        if (
            ($_SERVER["HTTP_X_REQUESTED_WITH"] ?? "") === "XMLHttpRequest"
        ) {
            http_response_code(204);
            return;
        }

        header("Location: /books/show?book_id=" . $bookId, true, 303);
        exit;
    }
}
