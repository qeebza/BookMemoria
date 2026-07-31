<?php

declare(strict_types=1);

namespace App\Controllers;

use Cloudinary\Cloudinary;
use PDO;
use RuntimeException;
use Src\Request;
use Throwable;

class BookController
{
    private const MAX_COVER_SIZE = 2 * 1024 * 1024;

    private const COVER_MIME_TYPES = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    public function __construct(
        private Request $request,
        private PDO $database,
        private Cloudinary $cloudinary
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

    public function edit(): void
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

        view("book-edit", [
            "book" => $book,
            "genres" => $this->findAllGenres(),
            "selectedGenreIds" => $this->findGenreIdsForBook($bookId)
        ]);
    }

    public function update(): void
    {
        $userId = $this->requireAuthenticatedUserId();
        $bookId = filter_var(
            $this->request->input("book_id"),
            FILTER_VALIDATE_INT
        );

        $currentBook = $bookId === false
            ? false
            : $this->findBookForUser($userId, $bookId);

        if (
            $bookId === false ||
            $currentBook === false
        ) {
            $this->renderNotFound();
            return;
        }

        $bookData = $this->getBookFormData();
        $genreIds = $this->normalizeGenreIds(
            $this->request->input("genre_ids", [])
        );
        $currentCoverPath = is_string($currentBook["cover_page_path"])
            ? $currentBook["cover_page_path"]
            : null;
        $currentCoverPublicId = is_string($currentBook["cover_public_id"])
            ? $currentBook["cover_public_id"]
            : null;
        $coverUpload = $this->request->file("cover");
        $validationError = $this->validateBookData($bookData);

        if ($validationError !== null) {
            $this->renderEditForm(
                $bookId,
                $bookData,
                $genreIds,
                $validationError,
                $currentCoverPath
            );
            return;
        }

        $coverValidationError = $this->validateCoverUpload($coverUpload);

        if ($coverValidationError !== null) {
            $this->renderEditForm(
                $bookId,
                $bookData,
                $genreIds,
                $coverValidationError,
                $currentCoverPath
            );
            return;
        }

        $newCover = null;

        try {
            if ($this->hasCoverUpload($coverUpload)) {
                $newCover = $this->storeCoverUpload($coverUpload);
            }

            $bookData["coverPagePath"] = $newCover["url"]
                ?? $currentCoverPath;
            $bookData["coverPublicId"] = $newCover["publicId"]
                ?? $currentCoverPublicId;

            $this->database->beginTransaction();

            $this->updateBookForUser($userId, $bookId, $bookData);
            $this->replaceBookGenres($bookId, $genreIds);
            $this->adjustReadingProgress(
                $userId,
                $bookId,
                (int) $bookData["totalPage"]
            );

            $this->database->commit();

            if ($newCover !== null) {
                $this->deleteCloudCover($currentCoverPublicId);
            }

            header(
                "Location: /books/show?book_id=" . $bookId,
                true,
                303
            );
            exit;
        } catch (Throwable) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }

            if ($newCover !== null) {
                $this->deleteCloudCover($newCover["publicId"]);
            }

            $this->renderEditForm(
                $bookId,
                $bookData,
                $genreIds,
                "The book could not be updated.",
                $currentCoverPath
            );
        }
    }

    public function delete(): void
    {
        $userId = $this->requireAuthenticatedUserId();
        $bookId = filter_var(
            $this->request->input("book_id"),
            FILTER_VALIDATE_INT
        );

        $book = $bookId === false
            ? false
            : $this->findBookForUser($userId, $bookId);

        if ($bookId === false || $book === false) {
            $this->renderNotFound();
            return;
        }

        $bookWasDeleted = false;

        try {
            $this->database->beginTransaction();

            $this->deleteUserBookData($userId, $bookId);

            if (!$this->bookHasReaders($bookId)) {
                $this->deleteBookGenres($bookId);
                $this->deleteBook($bookId);
                $bookWasDeleted = true;
            }

            $this->database->commit();
        } catch (Throwable) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }

            view("book-edit", [
                "error" => "The book could not be deleted.",
                "book" => $book,
                "genres" => $this->findAllGenres(),
                "selectedGenreIds" => $this->findGenreIdsForBook($bookId)
            ]);
            return;
        }

        if ($bookWasDeleted) {
            $coverPublicId = is_string($book["cover_public_id"])
                ? $book["cover_public_id"]
                : null;
            $this->deleteCloudCover($coverPublicId);
        }

        header("Location: /dashboard", true, 303);
        exit;
    }

    public function removeCover(): void
    {
        $userId = $this->requireAuthenticatedUserId();
        $bookId = filter_var(
            $this->request->input("book_id"),
            FILTER_VALIDATE_INT
        );

        $book = $bookId === false
            ? false
            : $this->findBookForUser($userId, $bookId);

        if ($bookId === false || $book === false) {
            $this->renderNotFound();
            return;
        }

        $coverPublicId = is_string($book["cover_public_id"])
            ? $book["cover_public_id"]
            : null;

        try {
            $this->clearBookCoverForUser($userId, $bookId);
        } catch (Throwable) {
            view("book-edit", [
                "error" => "The uploaded cover could not be removed.",
                "book" => $book,
                "genres" => $this->findAllGenres(),
                "selectedGenreIds" => $this->findGenreIdsForBook($bookId)
            ]);
            return;
        }

        $this->deleteCloudCover($coverPublicId);

        header("Location: /books/edit?book_id=" . $bookId, true, 303);
        exit;
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
            "isbn" => $this->normalizeIsbn($this->getStringInput("isbn")),
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

        if ($bookData["isbn"] !== "" && !$this->isValidIsbn($bookData["isbn"])) {
            return "ISBN must be a valid ISBN-10 or ISBN-13.";
        }

        return null;
    }

    private function normalizeIsbn(string $isbn): string
    {
        return strtoupper(str_replace(["-", " "], "", $isbn));
    }

    private function isValidIsbn(string $isbn): bool
    {
        if (preg_match('/^\d{13}$/', $isbn) === 1) {
            $sum = 0;

            for ($index = 0; $index < 13; $index++) {
                $sum += (int) $isbn[$index] * ($index % 2 === 0 ? 1 : 3);
            }

            return $sum % 10 === 0;
        }

        if (preg_match('/^\d{9}[\dX]$/', $isbn) !== 1) {
            return false;
        }

        $sum = 0;

        for ($index = 0; $index < 10; $index++) {
            $digit = $isbn[$index] === "X" ? 10 : (int) $isbn[$index];
            $sum += $digit * (10 - $index);
        }

        return $sum % 11 === 0;
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
            "isbn" => $bookData["isbn"],
            "description" => $bookData["description"],
            "totalPage" => $bookData["totalPage"],
            "genres" => $this->findAllGenres(),
            "selectedGenreIds" => $genreIds
        ]);
    }

    private function renderEditForm(
        int $bookId,
        array $bookData,
        array $genreIds,
        string $error,
        ?string $coverPath
    ): void
    {
        view("book-edit", [
            "error" => $error,
            "book" => [
                "book_id" => $bookId,
                "title" => $bookData["title"],
                "author" => $bookData["author"],
                "isbn" => $bookData["isbn"],
                "description" => $bookData["description"],
                "total_page" => $bookData["totalPage"],
                "cover_page_path" => $coverPath
            ],
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
                isbn,
                description,
                total_page
            )
            VALUES (
                :title,
                :author,
                :isbn,
                :description,
                :total_page
            )
            RETURNING book_id"
        );

        $bookInsertStatement->execute([
            "title" => $bookData["title"],
            "author" => $bookData["author"],
            "isbn" => $bookData["isbn"] === "" ? null : $bookData["isbn"],
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

    private function updateBookForUser(
        int $userId,
        int $bookId,
        array $bookData
    ): void
    {
        $bookUpdateStatement = $this->database->prepare(
            "UPDATE books
            SET title = :title,
                author = :author,
                isbn = :isbn,
                description = :description,
                total_page = :total_page,
                cover_page_path = :cover_page_path,
                cover_public_id = :cover_public_id
            WHERE book_id = :book_id
              AND EXISTS (
                  SELECT 1
                  FROM reading_records
                  WHERE reading_records.book_id = books.book_id
                    AND reading_records.user_id = :user_id
              )"
        );

        $bookUpdateStatement->execute([
            "title" => $bookData["title"],
            "author" => $bookData["author"],
            "isbn" => $bookData["isbn"] === "" ? null : $bookData["isbn"],
            "description" => $bookData["description"],
            "total_page" => (int) $bookData["totalPage"],
            "cover_page_path" => $bookData["coverPagePath"],
            "cover_public_id" => $bookData["coverPublicId"],
            "book_id" => $bookId,
            "user_id" => $userId
        ]);

        if ($bookUpdateStatement->rowCount() !== 1) {
            throw new RuntimeException("The book could not be updated.");
        }
    }

    private function adjustReadingProgress(
        int $userId,
        int $bookId,
        int $totalPage
    ): void
    {
        $progressStatement = $this->database->prepare(
            "UPDATE reading_records
            SET current_page = CASE
                WHEN book_status = 'completed' THEN :completed_total_page
                ELSE LEAST(current_page, :maximum_page)
            END
            WHERE user_id = :user_id
              AND book_id = :book_id"
        );

        $progressStatement->execute([
            "completed_total_page" => $totalPage,
            "maximum_page" => $totalPage,
            "user_id" => $userId,
            "book_id" => $bookId
        ]);
    }

    private function deleteUserBookData(int $userId, int $bookId): void
    {
        foreach (["quotes", "ratings", "reading_records"] as $table) {
            $statement = $this->database->prepare(
                "DELETE FROM {$table}
                WHERE user_id = :user_id
                  AND book_id = :book_id"
            );

            $statement->execute([
                "user_id" => $userId,
                "book_id" => $bookId
            ]);
        }
    }

    private function clearBookCoverForUser(int $userId, int $bookId): void
    {
        $statement = $this->database->prepare(
            "UPDATE books
            SET cover_page_path = NULL,
                cover_public_id = NULL
            WHERE book_id = :book_id
              AND EXISTS (
                  SELECT 1
                  FROM reading_records
                  WHERE reading_records.book_id = books.book_id
                    AND reading_records.user_id = :user_id
              )"
        );
        $statement->execute([
            "book_id" => $bookId,
            "user_id" => $userId
        ]);

        if ($statement->rowCount() !== 1) {
            throw new RuntimeException("The uploaded cover could not be removed.");
        }
    }

    private function bookHasReaders(int $bookId): bool
    {
        $statement = $this->database->prepare(
            "SELECT EXISTS (
                SELECT 1
                FROM reading_records
                WHERE book_id = :book_id
            )"
        );
        $statement->execute(["book_id" => $bookId]);

        return (bool) $statement->fetchColumn();
    }

    private function deleteBookGenres(int $bookId): void
    {
        $statement = $this->database->prepare(
            "DELETE FROM book_genres
            WHERE book_id = :book_id"
        );
        $statement->execute(["book_id" => $bookId]);
    }

    private function deleteBook(int $bookId): void
    {
        $statement = $this->database->prepare(
            "DELETE FROM books
            WHERE book_id = :book_id"
        );
        $statement->execute(["book_id" => $bookId]);

        if ($statement->rowCount() !== 1) {
            throw new RuntimeException("The book could not be deleted.");
        }
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

    private function replaceBookGenres(int $bookId, array $genreIds): void
    {
        $genreDeleteStatement = $this->database->prepare(
            "DELETE FROM book_genres
            WHERE book_id = :book_id"
        );

        $genreDeleteStatement->execute([
            "book_id" => $bookId
        ]);

        $this->attachGenres($bookId, $genreIds);
    }

    private function hasCoverUpload(?array $coverUpload): bool
    {
        return $coverUpload !== null &&
            ($coverUpload["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    private function validateCoverUpload(?array $coverUpload): ?string
    {
        if (!$this->hasCoverUpload($coverUpload)) {
            return null;
        }

        if (($coverUpload["error"] ?? null) !== UPLOAD_ERR_OK) {
            return "The cover image could not be uploaded.";
        }

        $fileSize = (int) ($coverUpload["size"] ?? 0);

        if ($fileSize <= 0 || $fileSize > self::MAX_COVER_SIZE) {
            return "The cover image must be smaller than 2 MB.";
        }

        $temporaryPath = $coverUpload["tmp_name"] ?? null;

        if (
            !is_string($temporaryPath) ||
            !is_uploaded_file($temporaryPath)
        ) {
            return "The uploaded cover image is invalid.";
        }

        $imageDetails = @getimagesize($temporaryPath);
        $mimeType = is_array($imageDetails)
            ? ($imageDetails["mime"] ?? null)
            : null;

        if (!is_string($mimeType) || !in_array($mimeType, self::COVER_MIME_TYPES, true)) {
            return "The cover must be a JPEG, PNG, or WebP image.";
        }

        return null;
    }

    private function storeCoverUpload(array $coverUpload): array
    {
        $temporaryPath = (string) $coverUpload["tmp_name"];
        $result = $this->cloudinary->uploadApi()->upload($temporaryPath, [
            "folder" => "bookmemoria/covers",
            "resource_type" => "image"
        ]);

        $url = $result["secure_url"] ?? null;
        $publicId = $result["public_id"] ?? null;

        if (!is_string($url) || !is_string($publicId)) {
            throw new RuntimeException("Cloudinary did not return the cover details.");
        }

        return [
            "url" => $url,
            "publicId" => $publicId
        ];
    }

    private function deleteCloudCover(?string $publicId): void
    {
        if ($publicId === null || $publicId === "") {
            return;
        }

        try {
            $this->cloudinary->uploadApi()->destroy($publicId, [
                "invalidate" => true,
                "resource_type" => "image"
            ]);
        } catch (Throwable) {
            // The database update is still valid if old-image cleanup fails.
        }
    }

    private function findBookForUser(int $userId, int $bookId): array|false
    {
        $bookStatement = $this->database->prepare(
            "SELECT
                books.book_id,
                books.title,
                books.author,
                books.isbn,
                books.description,
                books.total_page,
                books.cover_page_path,
                books.cover_public_id,
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

    private function findGenreIdsForBook(int $bookId): array
    {
        $genreStatement = $this->database->prepare(
            "SELECT genre_id
            FROM book_genres
            WHERE book_id = :book_id"
        );

        $genreStatement->execute([
            "book_id" => $bookId
        ]);

        return array_map(
            "intval",
            $genreStatement->fetchAll(PDO::FETCH_COLUMN)
        );
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
