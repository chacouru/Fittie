<?php
// db_connect.php
$host = 'localhost';
$dbname = 'fitty';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // エラー表示を削除またはログファイルに記録
    error_log('データベース接続エラー: ' . $e->getMessage());
    // echo文は削除
}
?>