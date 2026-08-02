<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Src\Request;

class DashboardController
{
    public function __construct(
        private Request $request,
        private PDO $database
    ) {
    }

    public function index(): void
    {
        if (!isset($_SESSION["user"])) {
            header("Location: /login");
            exit;
        }

        $search = $this->queryString("q");
        $genreId = filter_var(
            $this->request->query("genre_id"),
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1]]
        );

        $conditions = ["reading_records.user_id = :user_id"];
        $parameters = ["user_id" => $_SESSION["user"]["id"]];

        if ($search !== "") {
            $conditions[] = "(
                books.title ILIKE :search
                OR books.author ILIKE :search
                OR books.isbn ILIKE :search
            )";
            $parameters["search"] = "%" . $search . "%";
        }

        if ($genreId !== false) {
            $conditions[] = "EXISTS (
                SELECT 1
                FROM book_genres AS filtered_book_genres
                WHERE filtered_book_genres.book_id = books.book_id
                  AND filtered_book_genres.genre_id = :genre_id
            )";
            $parameters["genre_id"] = $genreId;
        }

        $sql = "SELECT
                books.book_id,
                books.title,
                books.author,
                books.isbn,
                books.total_page,
                books.cover_page_path,
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
            WHERE " . implode(" AND ", $conditions) . "
            GROUP BY
                books.book_id,
                reading_records.record_id
            ORDER BY books.title ASC";

        $statement = $this->database->prepare($sql);
        $statement->execute($parameters);

        $books = $statement->fetchAll(PDO::FETCH_ASSOC);

        view("dashboard", [
            "books" => $books,
            "genres" => $this->findAllGenres(),
            "filters" => [
                "q" => $search,
                "genreId" => $genreId === false ? "" : (string) $genreId
            ],
            "hasFilters" => $search !== "" ||
                $genreId !== false
        ]);
    }

    private function queryString(string $key): string
    {
        $value = $this->request->query($key, "");

        return is_string($value) ? trim(substr($value, 0, 100)) : "";
    }

    private function findAllGenres(): array
    {
        $statement = $this->database->query(
            "SELECT genre_id, genre_name
            FROM genres
            ORDER BY genre_name"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
