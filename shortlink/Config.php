<?php

namespace shortlink;

class Config {
    const DB_HOST = 'localhost';
    const DB_NAME = 'redirection_db';
    const DB_USER = 'redirector';
    const DB_PASSWORD = 'redirector_password_123';

    const MAX_SLUG_LENGTH = 50;
    const ALLOWED_SLUG_CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_~";
}