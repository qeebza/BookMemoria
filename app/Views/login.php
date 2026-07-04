<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - BookMemoria</title>
</head>
<body>
    <nav>
        <h1>BookMemoria</h1>

        <a href="/">Home</a>
        <a href="/login">Login</a>
    </nav>

    <main>
        <h2>Login</h2>

        <form action="/login" method="POST">
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
            </div>

            <button type="submit">Login</button>
        </form>
    </main>
</body>
</html>