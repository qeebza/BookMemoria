<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book["title"]) ?> - BookMemoria</title>
</head>
<body>
    <a href="/dashboard">&larr; Back to dashboard</a>

    <h1><?= htmlspecialchars($book["title"]) ?></h1>

    <p>
        Author: <?= htmlspecialchars($book["author"]) ?>
    </p>

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
