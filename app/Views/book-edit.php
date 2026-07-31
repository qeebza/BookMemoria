<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit <?= htmlspecialchars($book["title"]) ?> - BookMemoria</title>

    <style>
        .book-cover-preview,
        .book-cover-placeholder {
            width: 150px;
            height: 225px;
            border-radius: 6px;
        }

        .book-cover-preview {
            display: block;
            object-fit: cover;
        }

        .book-cover-preview[hidden] {
            display: none;
        }

        .book-cover-preview[hidden] {
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
    <a href="/books/show?book_id=<?= (int) $book["book_id"] ?>">
        &larr; Back to book
    </a>

    <h1>Edit Book</h1>

    <?php if (!empty($error)): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form
        action="/books/update"
        method="POST"
        enctype="multipart/form-data"
    >
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
            <label for="isbn">ISBN (optional)</label>
            <input
                type="text"
                id="isbn"
                name="isbn"
                value="<?= htmlspecialchars($book["isbn"] ?? "") ?>"
                maxlength="17"
                placeholder="9780385533225"
            >

            <small>
                ISBN-10 or ISBN-13.
                <a
                    href="https://isbnsearch.org/"
                    target="_blank"
                    rel="noopener noreferrer"
                >Find the ISBN on ISBN Search</a>
            </small>
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

        <div>
            <label for="cover">Book cover</label>

            <?php
                $previewCoverUrl = $book["cover_page_path"] ?? "";

                if ($previewCoverUrl === "" && !empty($book["isbn"])) {
                    $previewCoverUrl = "https://covers.openlibrary.org/b/isbn/"
                        . rawurlencode($book["isbn"])
                        . "-M.jpg?default=false";
                }
            ?>

            <?php if ($previewCoverUrl !== ""): ?>
                <div>
                    <img
                        class="book-cover-preview"
                        src="<?= htmlspecialchars($previewCoverUrl) ?>"
                        alt="Current cover of <?= htmlspecialchars($book["title"]) ?>"
                        onerror="this.hidden=true; this.nextElementSibling.hidden=false"
                    >

                    <span class="book-cover-placeholder" hidden>
                        No cover available
                    </span>
                </div>
            <?php endif; ?>

            <input
                type="file"
                id="cover"
                name="cover"
                accept="image/jpeg,image/png,image/webp"
            >

            <small>JPEG, PNG, or WebP. Maximum size: 2 MB.</small>
        </div>

        <?php if (!empty($book["cover_page_path"])): ?>
            <div>
                <button
                    type="submit"
                    form="remove-cover-form"
                >Remove Uploaded Cover</button>
            </div>
        <?php endif; ?>

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

    <?php if (!empty($book["cover_page_path"])): ?>
        <form
            id="remove-cover-form"
            action="/books/cover/remove"
            method="POST"
            onsubmit="return confirm('Remove the uploaded cover?')"
        >
            <input
                type="hidden"
                name="book_id"
                value="<?= (int) $book["book_id"] ?>"
            >
        </form>
    <?php endif; ?>

    <hr>

    <h2>Delete Book</h2>

    <p>This permanently removes the book and your related reading data.</p>

    <form
        action="/books/delete"
        method="POST"
        onsubmit="return confirm('Delete this book permanently?')"
    >
        <input
            type="hidden"
            name="book_id"
            value="<?= (int) $book["book_id"] ?>"
        >

        <button type="submit">Delete Book</button>
    </form>
</body>
</html>
