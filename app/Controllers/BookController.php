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

        view("book-create");
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
                "totalPage" => $totalPage
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
                "totalPage" => $totalPage
            ]);
        }
    }
}