<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BookMemoria</title>
</head>
<body>
    <nav>
        <h1>BookMemoria</h1>

        <a href="/">Home</a>

        <?php if (isset($_SESSION["user"])): ?>
            <a href="/dashboard">Dashboard</a>
            <a href="/books/create">Add Book</a>

            <form action="/logout" method="POST">
                <button type="submit">Logout</button>
            </form>
        <?php else: ?>
            <a href="/login">Login</a>
            <a href="/register">Register</a>
        <?php endif; ?>
    </nav>

    <main>
        <?php if (isset($_SESSION["user"])): ?>
            <p>
                Welcome,
                <?= htmlspecialchars($_SESSION["user"]["name"]) ?>
            </p>
        <?php endif; ?>

        <section>
            <h2>Track your books. Remember your reading journey.</h2>

            <p>
                BookMemoria helps you manage books you want to read,
                books you are currently reading, and books you have completed.
            </p>

            <?php if (isset($_SESSION["user"])): ?>
                <a href="/dashboard">View Dashboard</a>
                <a href="/books/create">Add a Book</a>
            <?php else: ?>
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>