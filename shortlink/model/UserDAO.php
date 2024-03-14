<?php

namespace shortlink;

use PDO;
use PDOException;

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';

class UserDAO {
    private static $instance = null;
    private $db;

    private function __construct() {
        $this->db = Database::get_instance();
    }

    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new UserDAO();
        }
        return self::$instance;
    }

    public function get_user_by_uid($uid) {
        $pdo = $this->db->get_pdo();
        if (is_null($pdo)) return null;

        try {
            $query = "SELECT * FROM users WHERE uid = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$uid]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }

        if (count($rows) != 1) return null;

        $row = $rows[0];
        return new User($row['username'], $row['uid'], $row['role']);
    }

    public function get_all_users() {
        $pdo = $this->db->get_pdo();
        if (is_null($pdo)) return null;

        try {
            $query = "SELECT * FROM users";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }

        $users = array();
        foreach ($rows as $row) {
            $users[] = new User($row['username'], $row['uid'], $row['role']);
        }

        return $users;
    }

    public function create_user($username, $password, $role) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $pdo = $this->db->get_pdo();
        if (is_null($pdo)) return null;

        try {
            $query = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";

            $stmt = $pdo->prepare($query);
            $stmt->execute([$username, $password_hash, $role]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
        
        return new User($username, $pdo->lastInsertId(), $role);
    }

    public function delete_user($user) {
        $uid = $user->get_uid();

        $pdo = $this->db->get_pdo();
        if (is_null($pdo)) return false;

        try {
            $query = "DELETE FROM users WHERE uid = ?";

            $stmt = $pdo->prepare($query);
            $stmt->execute([$uid]);

            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function update_user($user, $new_username, $new_password, $new_role) {
        $uid = $user->get_uid();
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $pdo = $this->db->get_pdo();
        if (is_null($pdo)) return false;

        try {
            $query = "UPDATE users SET username = ?, password = ?, role = ? WHERE uid = ?";

            $stmt = $pdo->prepare($query);
            $stmt->execute([$new_username, $new_password_hash, $new_role, $uid]);

            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
}