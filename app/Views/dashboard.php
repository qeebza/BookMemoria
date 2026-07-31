<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - BookMemoria</title>
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
                    <tr>
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
