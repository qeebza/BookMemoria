<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use RuntimeException;
use Src\Request;
use Throwable;

class BookController
{
    public function __construct(
        private Request $request,
        private PDO $database
    ) {
    }

    public function create(): void
    {
        $this->requireAuthenticatedUserId();

        view("book-create", [
            "genres" => $this->findAllGenres()
        ]);
    }

    public function store(): void
    {
        $userId = $this->requireAuthenticatedUserId();
        $bookData = $this->getBookFormData();
        $genreIds = $this->normalizeGenreIds(
            $this->request->input("genre_ids", [])
        );

        $validationError = $this->validateBookData($bookData);

        if ($validationError !== null) {
            $this->renderCreateForm(
                $bookData,
                $genreIds,
                $validationError
            );
            return;
        }

        try {
            $this->database->beginTransaction();

            $bookId = $this->insertBook($bookData);
            $this->createReadingRecord($userId, $bookId);
            $this->attachGenres($bookId, $genreIds);

            $this->database->commit();

            header("Location: /dashboard", true, 303);
            exit;
        } catch (Throwable) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }

            $this->renderCreateForm(
                $bookData,
                $genreIds,
                "The book could not be added."
            );
        }
    }

    public function show(): void
    {
        $userId = $this->requireAuthenticatedUserId();
        $bookId = filter_var(
            $this->request->query("book_id"),
            FILTER_VALIDATE_INT
        );

        if ($bookId === false) {
            $this->renderNotFound();
            return;
        }

        $book = $this->findBookForUser($userId, $bookId);

        if ($book === false) {
            $this->renderNotFound();
            return;
        }

        view("book-show", [
            "book" => $book,
            "quotes" => $this->findQuotesForUserBook($userId, $bookId),
            "rating" => $this->findRatingForUserBook($userId, $bookId)
        ]);
    }

    private function requireAuthenticatedUserId(): int
    {
        $userId = filter_var(
            $_SESSION["user"]["id"] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($userId === false) {
            header("Location: /login");
            exit;
        }

        return $userId;
    }

    private function getBookFormData(): array
    {
        return [
            "title" => $this->getStringInput("title"),
            "author" => $this->getStringInput("author"),
            "description" => $this->getStringInput("description"),
            "totalPage" => $this->request->input("total_page", "")
        ];
    }

    private function getStringInput(string $key): string
    {
        $value = $this->request->input($key, "");

        return is_string($value) ? trim($value) : "";
    }

    private function normalizeGenreIds(mixed $genreIds): array
    {
        if (!is_array($genreIds)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map("intval", $genreIds),
            static fn (int $genreId): bool => $genreId > 0
        )));
    }

    private function validateBookData(array $bookData): ?string
    {
        $totalPage = filter_var(
            $bookData["totalPage"],
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1]]
        );

        if (
            $bookData["title"] === "" ||
            $bookData["author"] === "" ||
            $totalPage === false
        ) {
            return "Title, author, and a valid page count are required.";
        }

        return null;
    }

    private function renderCreateForm(
        array $bookData,
        array $genreIds,
        string $error
    ): void
    {
        view("book-create", [
            "error" => $error,
            "title" => $bookData["title"],
            "author" => $bookData["author"],
            "description" => $bookData["description"],
            "totalPage" => $bookData["totalPage"],
            "genres" => $this->findAllGenres(),
            "selectedGenreIds" => $genreIds
        ]);
    }

    private function insertBook(array $bookData): int
    {
        $bookInsertStatement = $this->database->prepare(
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

        $bookInsertStatement->execute([
            "title" => $bookData["title"],
            "author" => $bookData["author"],
            "description" => $bookData["description"],
            "total_page" => (int) $bookData["totalPage"]
        ]);

        $bookId = $bookInsertStatement->fetchColumn();

        if ($bookId === false) {
            throw new RuntimeException("The inserted book ID was not returned.");
        }

        return (int) $bookId;
    }

    private function createReadingRecord(int $userId, int $bookId): void
    {
        $readingRecordStatement = $this->database->prepare(
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

        $readingRecordStatement->execute([
            "user_id" => $userId,
            "book_id" => $bookId
        ]);
    }

    private function attachGenres(int $bookId, array $genreIds): void
    {
        if ($genreIds === []) {
            return;
        }

        $genreInsertStatement = $this->database->prepare(
            "INSERT INTO book_genres (book_id, genre_id)
            VALUES (:book_id, :genre_id)"
        );

        foreach ($genreIds as $genreId) {
            $genreInsertStatement->execute([
                "book_id" => $bookId,
                "genre_id" => $genreId
            ]);
        }
    }

    private function findBookForUser(int $userId, int $bookId): array|false
    {
        $bookStatement = $this->database->prepare(
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

        $bookStatement->execute([
            "user_id" => $userId,
            "book_id" => $bookId
        ]);

        return $bookStatement->fetch(PDO::FETCH_ASSOC);
    }

    private function findQuotesForUserBook(int $userId, int $bookId): array
    {
        $quoteStatement = $this->database->prepare(
            "SELECT quote_id, quote_text, created_at
            FROM quotes
            WHERE user_id = :user_id
              AND book_id = :book_id
            ORDER BY created_at DESC"
        );

        $quoteStatement->execute([
            "user_id" => $userId,
            "book_id" => $bookId
        ]);

        return $quoteStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function findRatingForUserBook(int $userId, int $bookId): mixed
    {
        $ratingStatement = $this->database->prepare(
            "SELECT score
            FROM ratings
            WHERE user_id = :user_id
              AND book_id = :book_id"
        );

        $ratingStatement->execute([
            "user_id" => $userId,
            "book_id" => $bookId
        ]);

        return $ratingStatement->fetchColumn();
    }

    private function findAllGenres(): array
    {
        $genreStatement = $this->database->query(
            "SELECT genre_id, genre_name
            FROM genres
            ORDER BY genre_name"
        );

        return $genreStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function renderNotFound(): void
    {
        http_response_code(404);
        view("404");
    }
}
