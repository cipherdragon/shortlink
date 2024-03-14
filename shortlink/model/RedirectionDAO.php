<?php

namespace shortlink;

use PDOException;

require_once("Database.php");
require_once("Redirection.php");

class RedirectionDAO {
    private static $instance = NULL;
    private $db;

    private function __construct() {
        $this->db = Database::get_instance();
    }

    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new RedirectionDAO();
        }

        return self::$instance;
    }

    public function get_user_redirections($user) {
        $pdo = $this->db->get_pdo();
        if (is_null($pdo)) return NULL;

        try {
            $query = "SELECT slug, destination FROM redirects WHERE uid = ?";

            $stmt = $pdo->prepare($query);
            $stmt->execute([$user->get_uid()]);

            $result = $stmt->fetchAll();

            if (count($result) == 0) $result;

            $redirections = array();
            foreach ($result as $row) {
                $redirections[] = new Redirection($row['slug'], $row['destination']);
            }

            return $redirections;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return NULL;
        }
    }

    public function add_redirection($redirection, $user) {
        $pdo = $this->db->get_pdo();
        if (is_null($pdo)) return false;

        try {
            $query = "INSERT INTO redirects (slug, destination, uid) VALUES (?, ?, ?)";

            $stmt = $pdo->prepare($query);
            $stmt->execute([
                $redirection->get_slug(), 
                $redirection->get_destination(), 
                $user->get_uid()
            ]);

            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function update_redirection(Redirection $new_redirection) {
        $slug = $new_redirection->get_slug();
        $destination = $new_redirection->get_destination();

        $pdo = $this->db->get_pdo();
        if (is_null($pdo)) return false;

        try {
            $query = "UPDATE redirects SET destination = ? WHERE slug = ?";

            $stmt = $pdo->prepare($query);
            $stmt->execute([$destination, $slug]);

            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function delete_redirection($slug) {
        $pdo = $this->db->get_pdo();
        if (is_null($pdo)) return false;

        try {
            $query = "DELETE FROM redirects WHERE slug = ?";

            $stmt = $pdo->prepare($query);
            $stmt->execute([$slug]);

            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function get_redirection($slug) {
        $pdo = $this->db->get_pdo();
        if (is_null($pdo)) return NULL;

        try {
            $query = "SELECT destination FROM redirects WHERE slug = ?";

            $stmt = $pdo->prepare($query);
            $stmt->execute([$slug]);

            $result = $stmt->fetchAll();

            if (count($result) != 1) return NULL;

            return $result[0]['destination'];
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return NULL;
        }
    }
}