<?php

namespace shortlink;

require_once __DIR__ . '/model/Database.php';
require_once __DIR__ . '/Config.php';

// Return true if the installation is already done (check if the users table exists)
function check_installation_status() {
    $pdo = Database::get_instance()->get_pdo();
    if (is_null($pdo)) return false;

    $query = "SHOW TABLES LIKE 'users'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

function drop_tables() {
    $pdo = Database::get_instance()->get_pdo();
    if (is_null($pdo)) return;

    $pdo->exec("DROP TABLE IF EXISTS redirects");
    $pdo->exec("DROP TABLE IF EXISTS users");
}

function create_tables() {
    $pdo = Database::get_instance()->get_pdo();
    if (is_null($pdo)) return;

    $pdo->exec("CREATE TABLE users (
        uid INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(300) NOT NULL,
        role varchar(5) NOT NULL DEFAULT 'USER'
    )");

    $pdo->exec("CREATE TABLE redirects (
        slug VARCHAR(300) NOT NULL,
        destination VARCHAR(3000) NOT NULL,
        uid INT NOT NULL,
        FOREIGN KEY (uid) REFERENCES users(uid)
    )");
}

function create_admin_user() {
    $pdo = Database::get_instance()->get_pdo();
    if (is_null($pdo)) return;

    $query = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([Config::ADMIN_USERNAME, password_hash(Config::ADMIN_PASSWORD, PASSWORD_DEFAULT), "ADMIN"]);
}

function install() {
    drop_tables();
    create_tables();
    create_admin_user();

	header("Location: /rd");
    die();
}

if (!check_installation_status() || Config::RESET_DB) {
    install();
} else {
    echo "Installation is already done! Nothing to do.";
    die();
}
