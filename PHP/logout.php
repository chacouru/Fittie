<?php
require_once './login_function/functions.php';
session_start();

if (isset($_COOKIE['remember_me'])) {
    $hashed_token = hash('sha256', $_COOKIE['remember_me']);

    // デバッグ確認
    // echo "削除対象のハッシュ: " . $hashed_token;

    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
    $stmt->execute([$hashed_token]);

    // クッキー削除
    setcookie('remember_me', '', time() - 3600, '/', '', true, true);
}

// セッション削除
$_SESSION = [];
session_unset();
session_destroy();

header('Location: index.php');
exit;
