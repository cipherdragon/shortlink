<?php

namespace shortlink;

use PDO;

require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/User.php";
require_once __DIR__ . "/UserDAO.php";

class LoginManager {
    private static $instance = NULL;
    private $db = NULL;
    private $user = NULL;

    private final function __construct() {
        $this->db = Database::get_instance();
    }

    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new LoginManager();
        }

        return self::$instance;
    }

    public function login($username, $password) {
        $pdo = $this->db->get_pdo();
        if (is_null($pdo)) return false;

        $username = strtolower($username);
        if (!User::is_valid_username($username)) return false;

        try {
            $query = "SELECT uid, password, role FROM users WHERE username = ?";

            $stmt = $pdo->prepare($query);
            $stmt->execute([$username]);

            $result = $stmt->fetchAll();

            // If result row count is 0, nothing to return.
            // If more than 1, something is wrong. Login should fail.
            if (count($result) != 1) return false;

            $password_hash = $result[0]['password'];
            if (!password_verify($password, $password_hash)) return false;

            $this->user = new User($username, $result[0]['uid'], $result[0]['role']);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }

        if (session_status() != PHP_SESSION_ACTIVE) session_start();
        $_SESSION['uid'] = $this->user->get_uid();
        $_SESSION['last_activity'] = time();

        return true;
    }

    private function check_session_validity() {
        if (session_status() != PHP_SESSION_ACTIVE) session_start();

        if (!isset($_SESSION['uid']) || !isset($_SESSION['last_activity'])) {
            // $this->logout(); // Destroy session if invalid
            return false;
        }
        return (time() - $_SESSION['last_activity']) < 3600;
    }

    public function session_login() {
        if (session_status() != PHP_SESSION_ACTIVE) session_start();

        if (!$this->check_session_validity()) return false;
        $uid = $_SESSION['uid'];

        $pdo = $this->db->get_pdo();
        if (is_null($pdo)) return false;

        $user = UserDAO::get_instance()->get_user_by_uid($uid);
        if (is_null($user)) return false;
        $this->user = $user;

        $_SESSION['last_activity'] = time();
        return true;
    }

    public function logout() {
        $this->user = NULL;

        // return if session is not started
        if (session_status() == PHP_SESSION_NONE) return;

        $_SESSION = array();


        // Extracted from https://www.php.net/session_destroy
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
             // Ask browser to delete cookie by setting expiration time to past
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_unset();
        session_destroy();
    }

    public function get_user() {
        return $this->user;
    }
}