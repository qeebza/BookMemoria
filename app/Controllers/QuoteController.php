<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Src\Request;

class QuoteController
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

        $quoteText = trim(
            $this->request->input("quote_text", "")
        );

        if ($bookId === false || $quoteText === "") {
            http_response_code(422);
            echo "A valid book and quote are required.";
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
            "INSERT INTO quotes (
                user_id,
                book_id,
                quote_text
            )
            VALUES (
                :user_id,
                :book_id,
                :quote_text
            )"
        );

        $statement->execute([
            "user_id" => $_SESSION["user"]["id"],
            "book_id" => $bookId,
            "quote_text" => $quoteText
        ]);

        header("Location: /books/show?book_id=" . $bookId);
        exit;
    }
}
