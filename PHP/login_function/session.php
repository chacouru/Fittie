<?php
session_start();
require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $user_id = get_user_by_remember_token($_COOKIE['remember_me']);
    if ($user_id) {
        $_SESSION['user_id'] = $user_id;
    }
}
