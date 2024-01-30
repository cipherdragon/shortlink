<?php namespace shortlink; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to redirection creator!</title>
</head>
<body>
    <form method="post">
        <label for="username">Username: </label>
        <input type="text" name="username" placeholder="Username" required>
        <br><br>

        <label for="password">Password: </label>
        <input type="password" name="password" placeholder="Password" required>
        <br><br>

        <input type="submit" value="Login">
        <br><br>
    </form>
</body>
</html>

<?php
require_once __DIR__ . '/model/LoginManager.php';

$login_manager = LoginManager::get_instance();

if ($login_manager->session_login()) {
    header("Location: /rd");
    die();
}

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    die();
}

$username = $_POST['username'];
$password = $_POST['password'];

if ($login_manager->login($username, $password)) {
    header("Location: /rd");
    echo("Login successful!");
    die();
}

echo("Invalid username or password.");