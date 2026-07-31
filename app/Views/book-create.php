<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Book - BookMemoria</title>
</head>
<body>
    <h1>Add Book</h1>

    <?php if (!empty($error)): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="/books" method="POST">
        <div>
            <label for="title">Title</label>
            <input
                type="text"
                id="title"
                name="title"
                value="<?= htmlspecialchars($title ?? "") ?>"
            >
        </div>

        <div>
            <label for="author">Author</label>
            <input
                type="text"
                id="author"
                name="author"
                value="<?= htmlspecialchars($author ?? "") ?>"
            >
        </div>

        <div>
            <label for="description">Description</label>
            <textarea
                id="description"
                name="description"
            ><?= htmlspecialchars($description ?? "") ?></textarea>
        </div>

        <div>
            <label for="total_page">Total pages</label>
            <input
                type="number"
                id="total_page"
                name="total_page"
                min="1"
                value="<?= htmlspecialchars((string) ($totalPage ?? "")) ?>"
            >
        </div>

        <button type="submit">Add Book</button>
    </form>

    <a href="/dashboard">Back to dashboard</a>
</body>
</html>