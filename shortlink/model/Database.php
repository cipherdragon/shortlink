<?php

namespace shortlink;

use PDO;
use PDOException;

require_once __DIR__ . '/../Config.php';

class Database {
    private static $instance = NULL;

    private $db_user;
    private $db_password;
    private $connection_string;
    private $pdo;

    private final function __construct() {
        $this->db_user = Config::DB_USER;
        $this->db_password = Config::DB_PASSWORD;
        $this->connection_string = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME;
    }

    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function get_pdo() {
        $this->pdo = NULL;

        try {
            $this->pdo = new PDO($this->connection_string, $this->db_user, $this->db_password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return NULL;
        }

        return $this->pdo;
    }
}