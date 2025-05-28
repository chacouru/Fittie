<?php
require_once __DIR__ . '/functions.php';
session_start();

$email = $_POST['email'];
$password = $_POST['password'];
$remember = isset($_POST['remember_me']);

$pdo = db_connect();
$stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];

    if ($remember) {
        $token = generate_token();
        save_remember_token($user['id'], $token);
        setcookie('remember_me', $token, time() + (86400 * 30), '/', '', true, true);
    }

    header("Location: ../index.php");
    exit;
} else {
     echo '<script>
        alert("ログインに失敗しました。メールアドレスまたはパスワードが間違っています。");
        window.location.href = "../login.php";
    </script>';
    exit;
}