<?php

namespace shortlink;

class User {
    private $username;
    private $is_admin;
    private $uid;

    public function __construct($username, $uid, $role) {
        $this->username = $username;
        $this->is_admin = $role == "ADMIN";
        $this->uid = $uid;
    }

    public function get_username() {
        return $this->username;
    }

    public function get_uid() {
        return $this->uid;
    }

    public function is_admin() {
        return $this->is_admin;
    }

    public static function is_valid_username($username) {
        if (!is_string($username)) return false;
        if (strlen($username) > 50) return false;
        if ($username == "") return false;
        if (!preg_match("/^[a-z][a-z0-9_]*$/", $username)) return false;
        return true;
    }

    public static function is_valid_role($role) {
        if (!is_string($role)) return false;
        if ($role != "ADMIN" && $role != "USER") return false;
        return true;
    }
}