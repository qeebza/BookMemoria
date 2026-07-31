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

    <form action="/logout" method="POST">
        <button type="submit">Logout</button>
    </form>
</body>
</html>