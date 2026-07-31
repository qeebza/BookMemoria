<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - BookMemoria</title>

    <style>
        .book-cover-thumbnail,
        .book-cover-placeholder {
            width: 120px;
            height: 180px;
            border-radius: 6px;
        }

        .book-cover-thumbnail {
            display: block;
            object-fit: cover;
        }

        .book-cover-thumbnail[hidden] {
            display: none;
        }

        .book-cover-thumbnail[hidden] {
            display: none;
        }

        .book-cover-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            padding: 10px;
            background: #eeeeee;
            color: #666666;
            text-align: center;
        }

        .book-cover-placeholder[hidden] {
            display: none;
        }
    </style>
</head>
<body>
    <h1>Dashboard</h1>

    <p>
        Welcome, <?= htmlspecialchars($_SESSION["user"]["name"]) ?>
    </p>

    <a href="/">Home</a>
    <a href="/books/create">Add Book</a>

    <h2>My Books</h2>

    <?php if (empty($books)): ?>
        <p>You have not added any books yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genres</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Started</th>
                    <th>Completed</th>
                    <th>Update</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($books as $book): ?>
                    <?php
                        $coverUrl = $book["cover_page_path"] ?? "";

                        if ($coverUrl === "" && !empty($book["isbn"])) {
                            $coverUrl = "https://covers.openlibrary.org/b/isbn/"
                                . rawurlencode($book["isbn"])
                                . "-M.jpg?default=false";
                        }
                    ?>

                    <tr>
                        <td>
                            <a href="/books/show?book_id=<?= (int) $book["book_id"] ?>">
                                <?php if ($coverUrl !== ""): ?>
                                    <img
                                        class="book-cover-thumbnail"
                                        src="<?= htmlspecialchars($coverUrl) ?>"
                                        alt="Cover of <?= htmlspecialchars($book["title"]) ?>"
                                        loading="lazy"
                                        onerror="this.hidden=true; this.nextElementSibling.hidden=false"
                                    >

                                    <span class="book-cover-placeholder" hidden>
                                        No cover
                                    </span>
                                <?php else: ?>
                                    <span class="book-cover-placeholder">No cover</span>
                                <?php endif; ?>
                            </a>
                        </td>

                        <td>
                            <a href="/books/show?book_id=<?= (int) $book["book_id"] ?>">
                                <?= htmlspecialchars($book["title"]) ?>
                            </a>
                        </td>

                        <td>
                            <?= htmlspecialchars($book["author"]) ?>
                        </td>

                        <td>
                            <?= !empty($book["genres"])
                                ? htmlspecialchars($book["genres"])
                                : "No genres" ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                str_replace("_", " ", $book["book_status"])
                            ) ?>
                        </td>

                        <td>
                            <?= (int) $book["current_page"] ?>
                            /
                            <?= (int) $book["total_page"] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($book["read_date"] ?? "-") ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($book["completion_date"] ?? "-") ?>
                        </td>

                        <td>
                            <form
                                action="/reading-records/update"
                                method="POST"
                            >
                                <input
                                    type="hidden"
                                    name="book_id"
                                    value="<?= (int) $book["book_id"] ?>"
                                >

                                <select name="book_status">
                                    <option
                                        value="want_to_read"
                                        <?= $book["book_status"] === "want_to_read"
                                            ? "selected"
                                            : "" ?>
                                    >
                                        Want to read
                                    </option>

                                    <option
                                        value="reading"
                                        <?= $book["book_status"] === "reading"
                                            ? "selected"
                                            : "" ?>
                                    >
                                        Reading
                                    </option>

                                    <option
                                        value="completed"
                                        <?= $book["book_status"] === "completed"
                                            ? "selected"
                                            : "" ?>
                                    >
                                        Completed
                                    </option>
                                </select>

                                <input
                                    type="number"
                                    name="current_page"
                                    min="0"
                                    max="<?= (int) $book["total_page"] ?>"
                                    value="<?= (int) $book["current_page"] ?>"
                                >

                                <button type="submit">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <form action="/logout" method="POST">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
