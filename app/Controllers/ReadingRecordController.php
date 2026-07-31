<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Src\Request;

class ReadingRecordController
{
    public function __construct(
        private Request $request,
        private PDO $database
    ) {
    }

    public function update(): void
    {
        if (!isset($_SESSION["user"])) {
            header("Location: /login");
            exit;
        }

        $bookId = filter_var(
            $this->request->input("book_id"),
            FILTER_VALIDATE_INT
        );

        $currentPage = filter_var(
            $this->request->input("current_page"),
            FILTER_VALIDATE_INT
        );

        $status = $this->request->input("book_status");

        $allowedStatuses = [
            "want_to_read",
            "reading",
            "completed"
        ];

        if (
            $bookId === false ||
            $currentPage === false ||
            $currentPage < 0 ||
            !in_array($status, $allowedStatuses, true)
        ) {
            echo "Invalid reading record.";
            return;
        }

        $statement = $this->database->prepare(
            "SELECT books.total_page
             FROM reading_records
             INNER JOIN books
                ON books.book_id = reading_records.book_id
             WHERE reading_records.user_id = :user_id
               AND reading_records.book_id = :book_id"
        );

        $statement->execute([
            "user_id" => $_SESSION["user"]["id"],
            "book_id" => $bookId
        ]);

        $book = $statement->fetch(PDO::FETCH_ASSOC);

        if ($book === false) {
            http_response_code(404);
            echo "Reading record not found.";
            return;
        }

        $totalPage = (int) $book["total_page"];

        if ($currentPage > $totalPage) {
            echo "Current page cannot exceed total pages.";
            return;
        }

        if ($status === "want_to_read") {
            $currentPage = 0;
        }

        if ($status === "completed") {
            $currentPage = $totalPage;
        }

        $statement = $this->database->prepare(
            "UPDATE reading_records
            SET book_status = :book_status,
                current_page = :current_page,

                read_date = CASE
                    WHEN :read_status IN ('reading', 'completed')
                    THEN COALESCE(read_date, CURRENT_DATE)
                    ELSE NULL
                END,

                completion_date = CASE
                    WHEN :completion_status = 'completed'
                    THEN CURRENT_DATE
                    ELSE NULL
                END

            WHERE user_id = :user_id
            AND book_id = :book_id"
        );

        $statement->execute([
            "book_status" => $status,
            "current_page" => $currentPage,
            "read_status" => $status,
            "completion_status" => $status,
            "user_id" => $_SESSION["user"]["id"],
            "book_id" => $bookId
        ]);

        header("Location: /dashboard");
        exit;
    }
}