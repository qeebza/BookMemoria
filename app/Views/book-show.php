<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book["title"]) ?> - BookMemoria</title>

    <style>
        .book-cover,
        .book-cover-placeholder {
            width: 200px;
            height: 300px;
            border-radius: 6px;
        }

        .book-cover {
            display: block;
            object-fit: cover;
        }

        .book-cover[hidden] {
            display: none;
        }

        .book-cover-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            padding: 12px;
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
    <a href="/dashboard">&larr; Back to dashboard</a>

    <a href="/books/edit?book_id=<?= (int) $book["book_id"] ?>">
        Edit Book
    </a>

    <h1><?= htmlspecialchars($book["title"]) ?></h1>

    <?php
        $coverUrl = optimized_cover_url(
            $book["cover_page_path"] ?? null,
            400,
            600
        );
    ?>

    <?php if ($coverUrl !== ""): ?>
        <img
            class="book-cover"
            src="<?= htmlspecialchars($coverUrl) ?>"
            alt="Cover of <?= htmlspecialchars($book["title"]) ?>"
            onerror="this.hidden=true; this.nextElementSibling.hidden=false"
        >

        <div class="book-cover-placeholder" hidden>No cover available</div>
    <?php else: ?>
        <div class="book-cover-placeholder">No cover available</div>
    <?php endif; ?>

    <p>
        Author: <?= htmlspecialchars($book["author"]) ?>
    </p>

    <?php if (!empty($book["isbn"])): ?>
        <?php $encodedIsbn = rawurlencode($book["isbn"]); ?>

        <p>
            ISBN:
            <a
                href="https://isbnsearch.org/isbn/<?= $encodedIsbn ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                <?= htmlspecialchars($book["isbn"]) ?>
            </a>

            &middot;

            <a
                href="https://covers.openlibrary.org/b/isbn/<?= $encodedIsbn ?>-L.jpg?default=false"
                target="_blank"
                rel="noopener noreferrer"
            >Open cover image</a>
        </p>
    <?php endif; ?>

    <p>
        Genres:
        <?= !empty($book["genres"])
            ? htmlspecialchars($book["genres"])
            : "No genres" ?>
    </p>

    <p>
        Status:
        <?= htmlspecialchars(
            str_replace("_", " ", $book["book_status"])
        ) ?>
    </p>

    <p>
        Progress:
        <?= (int) $book["current_page"] ?>
        /
        <?= (int) $book["total_page"] ?>
    </p>

    <p>
        Started:
        <?= htmlspecialchars($book["read_date"] ?? "-") ?>
    </p>

    <p>
        Completed:
        <?= htmlspecialchars($book["completion_date"] ?? "-") ?>
    </p>

    <h2>Description</h2>

    <p>
        <?= nl2br(htmlspecialchars($book["description"] ?? "")) ?>
    </p>

    <h2>Rating</h2>

    <form id="rating-form" action="/ratings" method="POST">
        <input
            type="hidden"
            name="book_id"
            value="<?= (int) $book["book_id"] ?>"
        >

        <label for="score">Your rating</label>

        <select id="score" name="score">
            <?php for ($score = 1; $score <= 5; $score++): ?>
                <option
                    value="<?= $score ?>"
                    <?= (float) $rating === (float) $score
                        ? "selected"
                        : "" ?>
                >
                    <?= $score ?>
                </option>
            <?php endfor; ?>
        </select>

        <button type="submit">
            <?= $rating === false ? "Save Rating" : "Update Rating" ?>
        </button>
    </form>

    <p id="rating-message"></p>

    <?php if ($rating !== false): ?>
        <form action="/ratings/delete" method="POST">
            <input
                type="hidden"
                name="book_id"
                value="<?= (int) $book["book_id"] ?>"
            >

            <button type="submit">Remove Rating</button>
        </form>
    <?php endif; ?>

    <h2>Quotes</h2>

    <form action="/quotes" method="POST">
        <input
            type="hidden"
            name="book_id"
            value="<?= (int) $book["book_id"] ?>"
        >

        <div>
            <label for="quote_text">Quote</label>

            <textarea
                id="quote_text"
                name="quote_text"
                required
            ></textarea>
        </div>

        <button type="submit">Save Quote</button>
    </form>

    <?php if (empty($quotes)): ?>
        <p>No quotes saved yet.</p>
    <?php else: ?>
        <?php foreach ($quotes as $quote): ?>
            <blockquote>
                <?= nl2br(htmlspecialchars($quote["quote_text"])) ?>
            </blockquote>

            <small>
                <?= htmlspecialchars($quote["created_at"]) ?>
            </small>

            <form action="/quotes/update" method="POST">
                <input
                    type="hidden"
                    name="quote_id"
                    value="<?= (int) $quote["quote_id"] ?>"
                >

                <input
                    type="hidden"
                    name="book_id"
                    value="<?= (int) $book["book_id"] ?>"
                >

                <textarea
                    name="quote_text"
                    required
                ><?= htmlspecialchars($quote["quote_text"]) ?></textarea>

                <button type="submit">Update Quote</button>
            </form>

            <form action="/quotes/delete" method="POST">
                <input
                    type="hidden"
                    name="quote_id"
                    value="<?= (int) $quote["quote_id"] ?>"
                >

                <input
                    type="hidden"
                    name="book_id"
                    value="<?= (int) $book["book_id"] ?>"
                >

                <button type="submit">Delete Quote</button>
            </form>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
        const ratingForm = document.querySelector("#rating-form");
        const ratingMessage = document.querySelector("#rating-message");

        ratingForm.addEventListener("submit", async (event) => {
            event.preventDefault();

            const button = ratingForm.querySelector("button");
            button.disabled = true;
            ratingMessage.textContent = "Saving rating...";

            try {
                const response = await fetch(ratingForm.action, {
                    method: "POST",
                    body: new FormData(ratingForm),
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }

                if (!response.ok) {
                    throw new Error("Rating could not be saved.");
                }

                ratingMessage.textContent = "Rating saved.";
                button.textContent = "Update Rating";
            } catch (error) {
                ratingMessage.textContent = error.message;
            } finally {
                button.disabled = false;
            }
        });
    </script>

</body>
</html>
