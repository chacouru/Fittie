<?php
// db_connect.php
$host = 'localhost'; // データベースのホスト名
$dbname = 'fitty'; // データベース名
$username = 'root'; // データベースのユーザー名
$password = ''; // データベースのパスワード（必要に応じて設定）

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit;
}
?>
