<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;

class DashboardController
{
    public function __construct(private PDO $database)
    {
    }

    public function index(): void
    {
        if (!isset($_SESSION["user"])) {
            header("Location: /login");
            exit;
        }

        $statement = $this->database->prepare(
            "SELECT
                books.book_id,
                books.title,
                books.author,
                books.total_page,
                reading_records.book_status,
                reading_records.current_page,
                reading_records.read_date,
                reading_records.completion_date,
                STRING_AGG(
                    DISTINCT genres.genre_name,
                    ', ' ORDER BY genres.genre_name
                ) AS genres
            FROM reading_records
            INNER JOIN books
                ON books.book_id = reading_records.book_id
            LEFT JOIN book_genres
                ON book_genres.book_id = books.book_id
            LEFT JOIN genres
                ON genres.genre_id = book_genres.genre_id
            WHERE reading_records.user_id = :user_id
            GROUP BY
                books.book_id,
                reading_records.record_id
            ORDER BY books.title"
        );

        $statement->execute([
            "user_id" => $_SESSION["user"]["id"]
        ]);

        $books = $statement->fetchAll(PDO::FETCH_ASSOC);

        view("dashboard", [
            "books" => $books
        ]);
    }
}
