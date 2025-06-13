<?php
//login.phpからPOST受信
require_once __DIR__ . '/functions.php';

session_start();

// フォームから受け取るデータ
$name     = $_POST['name'];
$email    = $_POST['email'];
// パスワードだけハッシュ
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
$address  = $_POST['address'];
$phone    = $_POST['phone'];

// DB接続してINSERT
$pdo = db_connect();
$stmt = $pdo->prepare("
    INSERT INTO users (name, email, password, address, phone)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$name, $email, $password, $address, $phone]);

// 完了後にリダイレクト
header("Location: ../index.php");
exit;
