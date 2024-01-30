<?php

namespace shortlink;

require_once __DIR__ . '/../Config.php';

class Redirection {
    private $slug;
    private $destination;

    public function __construct($slug, $destination) {
        $this->slug = $slug;
        $this->destination = $destination;
    }

    public function get_slug() {
        return $this->slug;
    }

    public function get_destination() {
        return $this->destination;
    }

    public function get_properties() {
        return [
            "slug" => $this->slug,
            "destination" => $this->destination
        ];
    }

    public static function is_valid_slug($uri) {
        if (strlen($uri) > Config::MAX_SLUG_LENGTH) return false;
        
        $allowed_chars = mb_str_split(Config::ALLOWED_SLUG_CHARS);
        foreach (mb_str_split($uri) as $char) {
            if (!in_array($char, $allowed_chars)) return false;
        }

        return true;
    }
}