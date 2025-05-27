<?php
require_once __DIR__ . '/session.php';

if (isset($_SESSION['user_id'])) {
    echo "ログイン中：ユーザーID " . $_SESSION['user_id'];
} else {
    echo '<a href="login.php">ログイン</a> または <a href="register.php">登録</a>';
}