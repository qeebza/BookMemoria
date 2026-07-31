<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit <?= htmlspecialchars($book["title"]) ?> - BookMemoria</title>
</head>
<body>
    <a href="/books/show?book_id=<?= (int) $book["book_id"] ?>">
        &larr; Back to book
    </a>

    <h1>Edit Book</h1>

    <?php if (!empty($error)): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="/books/update" method="POST">
        <input
            type="hidden"
            name="book_id"
            value="<?= (int) $book["book_id"] ?>"
        >

        <div>
            <label for="title">Title</label>
            <input
                type="text"
                id="title"
                name="title"
                value="<?= htmlspecialchars($book["title"]) ?>"
                required
            >
        </div>

        <div>
            <label for="author">Author</label>
            <input
                type="text"
                id="author"
                name="author"
                value="<?= htmlspecialchars($book["author"]) ?>"
                required
            >
        </div>

        <div>
            <label for="description">Description</label>
            <textarea
                id="description"
                name="description"
            ><?= htmlspecialchars($book["description"] ?? "") ?></textarea>
        </div>

        <div>
            <label for="total_page">Total pages</label>
            <input
                type="number"
                id="total_page"
                name="total_page"
                min="1"
                value="<?= htmlspecialchars((string) $book["total_page"]) ?>"
                required
            >
        </div>

        <fieldset>
            <legend>Genres</legend>

            <?php foreach ($genres as $genre): ?>
                <?php $genreId = (int) $genre["genre_id"]; ?>

                <label>
                    <input
                        type="checkbox"
                        name="genre_ids[]"
                        value="<?= $genreId ?>"
                        <?= in_array(
                            $genreId,
                            $selectedGenreIds,
                            true
                        ) ? "checked" : "" ?>
                    >

                    <?= htmlspecialchars($genre["genre_name"]) ?>
                </label>
            <?php endforeach; ?>
        </fieldset>

        <button type="submit">Update Book</button>
    </form>
</body>
</html>
