# BookMemoria

BookMemoria is a personal book-tracking web application built from scratch with PHP and a small custom MVC framework.

## Features

- User registration, login, logout, and protected pages
- Personal reading dashboard
- Book details, genres, reading status, and page progress
- Reading start and completion dates
- Quotes and ratings
- Book editing and deletion
- Cloudinary book-cover uploads and removal
- Optional ISBN-10 and ISBN-13 storage
- ISBN Search links for finding book information
- Direct ISBN-based cover image links

## Tech stack

- PHP 8
- PostgreSQL 17
- Docker Compose
- HTML and JavaScript
- Cloudinary PHP SDK
- PHP dotenv

## Requirements

- PHP 8.0 or newer
- Composer
- Docker Desktop
- PHP extensions: `pdo_pgsql`, `pgsql`, `openssl`, and `curl`
- A Cloudinary account

## Setup

1. Start PostgreSQL:

   ```powershell
   docker compose up -d
   ```

   PostgreSQL is available on host port `5433`; the container uses port `5432`.

2. Install PHP dependencies:

   ```powershell
   composer install
   ```

3. Copy `.env.example` to `.env` and add the Cloudinary URL from your Cloudinary dashboard:

   ```env
   CLOUDINARY_URL=cloudinary://your_api_key:your_api_secret@your_cloud_name
   ```

   Never commit `.env` because it contains your API secret.

4. Run pending SQL files from `database/migrations` using pgAdmin's Query Tool.

5. Start the application:

   ```powershell
   php -S localhost:8000 -t public
   ```

6. Open [http://localhost:8000](http://localhost:8000).

## Finding a book or cover by ISBN

Enter an ISBN-10 or ISBN-13 when adding or editing a book. The book details page then links to its ISBN Search record and builds the cover URL automatically using that ISBN.

Open Library cover URL format:

```text
https://covers.openlibrary.org/b/isbn/YOUR_ISBN-L.jpg?default=false
```

Example:

```text
https://covers.openlibrary.org/b/isbn/9780385533225-L.jpg?default=false
```

## Project status

Currently under development as a backend and MVC learning project.
