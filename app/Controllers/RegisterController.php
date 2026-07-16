<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use PDOExeption;
use Src\Request;

class RegisterController {
    public function __construct(
        private Request $request,
        private PDO $database
        ) {
    }

    public function index(): void {
        view("register");
    }

    public function register(): void {
        $name = $this->request->input("name");
        $email = $this->request->input("email");
        $password = $this->request->input("password");
        $passwordConfirmation = $this->request->input("password_confirmation");

        $error = $this->validateRegistration(
            $name,
            $email,
            $password,
            $passwordConfirmation
        );

        if ($error !== null) {
            view("register", [
                "error" => $error,
                "name" => $name,
                "email" => $email
            ]);

            return;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $statement = $this->database->prepare(
                "INSERT INTO users (name, email, password)
                VALUES (:name, :email, :password)"
            );

            $statement->execute([
                "name" => $name,
                "email" => $email,
                "password" => $hashedPassword
            ]);

            echo "Account created successfully.";
        } catch (PDOException $exception) {
            view("register", [
                "error" => "An account with this email already exist.",
                "name" => $name,
                "email" => $email
            ]);
        }
    }

    private function validateRegistration(
        string $name,
        string $email,
        string $password,
        string $passwordConfirmation
    ): ?string {

        if (
            empty($name) ||
            empty($email) ||
            empty($password) ||
            empty($passwordConfirmation)
        ) { return "All fields are required."; }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Please enter a valid email address.";
        }

        if (strlen($password) < 8) {
            return "Password must contain at least 8 characters.";
        }

        if ($password !== $passwordConfirmation) {
            return "Password confirmation does not match.";
        }

        return null;
    }
}