<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Src\Request;

class LoginController {
    public function __construct(
        private Request $request,
        private PDO $database
        ) {
    }

    public function index(): void {
        view("login");
    }

    public function login(): void {
        $email = $this->request->input("email");
        $password = $this->request->input("password");

        $error = $this->validateLogin($email, $password);

        if ($error !== null) {
            view("login", [
                "error" => $error, 
                "email" => $email
            ]);
            return;
        }

        $statement = $this->database->prepare(
            "SELECT id, name, email, password
            FROM users
            WHERE email = :email
            LIMIT 1"
        );

        $statement->execute([
            "email" => $email
        ]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (
            $user === false ||
            !password_verify($password, $user["password"])
        ) {
            view("login", [
                "error" => "Invalid email or password.",
                "email" => $email
            ]);

            return;
        }

        session_regenerate_id(true);

        $_SESSION["user"] = [
            "id" => $user["id"],
            "name" => $user["name"],
            "email" => $user["email"]
        ];

        header("Location: /dashboard");
        exit;
    }

    private function validateLogin(string $email, string $password): ?string {
        if (empty($email) || empty($password)) {
            return "Email and password are required.";
        }

        if(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
            return "Please enter a valid email address.";
        }

        return null;
    }
}