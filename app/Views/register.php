<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - BookMemoria</title>
</head>
<body>
    <nav>
        <h1>BookMemoria</h1>

        <a href="/">Home</a>
        <a href="/login">Login</a>
        <a href="/register">Register</a>
    </nav>

    <main>
        <h2>Create Account</h2>

        <form action="/register" method="POST">
            <div>
                <label for="name">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                >
            </div>

            <div>
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                >
            </div>

            <div>
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                >
            </div>

            <div>
                <label for="password_confirmation">Confirm Password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                >
            </div>

            <button type="submit">Register</button>
        </form>
    </main>
</body>
</html>