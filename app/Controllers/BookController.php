<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Throwable;

class BookController {
    public function __construct(private PDO $database){
    }

    public function create():void {
        if (!isset($_SESSION["user"])) {
            header("Location: /login");
            exit;
        }

        view("book-create", [
            "genres" => $this->getGenres()
        ]);
    }

    public function store(): void {
        if (!isset($_SESSION["user"])) {
            header("Location: /login");
            exit;
        }

        $title = trim($_POST["title"] ?? "");
        $author = trim($_POST["author"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $totalPage = $_POST["total_page"] ?? "";
        $genreIds = $_POST["genre_ids"] ?? [];

        if (!is_array($genreIds)) {
            $genreIds = [];
        }

        $genreIds = array_values(array_unique(array_filter(
            array_map("intval", $genreIds),
            static fn (int $genreId): bool => $genreId > 0
        )));

        if (
            $title === "" ||
            $author === "" ||
            filter_var(
                $totalPage,
                FILTER_VALIDATE_INT,
                ["options" => ["min_range" => 1]]
            ) === false
        ) {
            view("book-create", [
                "error" => "Title, author, and a valid page count are required.",
                "title" => $title,
                "author" => $author,
                "description" => $description,
                "totalPage" => $totalPage,
                "genres" => $this->getGenres(),
                "selectedGenreIds" => $genreIds
            ]);

            return;
        }

        try {
            $this->database->beginTransaction();

            $statement = $this->database->prepare(
                "INSERT INTO books (
                    title,
                    author,
                    description,
                    total_page
                )
                VALUES (
                    :title,
                    :author,
                    :description,
                    :total_page
                )
                RETURNING book_id"
            );

            $statement->execute([
                "title" => $title,
                "author" => $author,
                "description" => $description,
                "total_page" => $totalPage
            ]);

            $bookId = $statement->fetchColumn();

            $statement = $this->database->prepare(
                "INSERT INTO reading_records (
                    user_id,
                    book_id,
                    book_status,
                    current_page
                )
                VALUES (
                    :user_id,
                    :book_id,
                    'want_to_read',
                    0
                )"
            );

            $statement->execute([
                "user_id" => $_SESSION["user"]["id"],
                "book_id" => $bookId
            ]);

            $genreStatement = $this->database->prepare(
                "INSERT INTO book_genres (book_id, genre_id)
                VALUES (:book_id, :genre_id)"
            );

            foreach ($genreIds as $genreId) {
                $genreStatement->execute([
                    "book_id" => $bookId,
                    "genre_id" => $genreId
                ]);
            }

            $this->database->commit();

            header("Location: /dashboard");
            exit;
        } catch (Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }

            view("book-create", [
                "error" => "The book could not be added.",
                "title" => $title,
                "author" => $author,
                "description" => $description,
                "totalPage" => $totalPage,
                "genres" => $this->getGenres(),
                "selectedGenreIds" => $genreIds
            ]);
        }
    }

    public function show(): void {
        if (!isset($_SESSION["user"])) {
            header("Location: /login");
            exit;
        }

        $bookId = filter_var(
            $_GET["book_id"] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($bookId === false || $bookId === null) {
            http_response_code(404);
            view("404");
            return;
        }

        $statement = $this->database->prepare(
            "SELECT
                books.book_id,
                books.title,
                books.author,
                books.description,
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
            WHERE reading_records.user_id = :user_id
              AND books.book_id = :book_id
            GROUP BY
                books.book_id,
                reading_records.record_id"
        );

        $statement->execute([
            "user_id" => $_SESSION["user"]["id"],
            "book_id" => $bookId
        ]);

        $book = $statement->fetch(PDO::FETCH_ASSOC);

        if ($book === false) {
            http_response_code(404);
            view("404");
            return;
        }

        $statement = $this->database->prepare(
            "SELECT quote_id, quote_text, created_at
            FROM quotes
            WHERE user_id = :user_id
              AND book_id = :book_id
            ORDER BY created_at DESC"
        );

        $statement->execute([
            "user_id" => $_SESSION["user"]["id"],
            "book_id" => $bookId
        ]);

        $quotes = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statement = $this->database->prepare(
            "SELECT score
            FROM ratings
            WHERE user_id = :user_id
              AND book_id = :book_id"
        );

        $statement->execute([
            "user_id" => $_SESSION["user"]["id"],
            "book_id" => $bookId
        ]);

        $rating = $statement->fetchColumn();

        view("book-show", [
            "book" => $book,
            "quotes" => $quotes,
            "rating" => $rating
        ]);
    }

    private function getGenres(): array {
        $statement = $this->database->query(
            "SELECT genre_id, genre_name
            FROM genres
            ORDER BY genre_name"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
