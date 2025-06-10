<?php
session_start();
header('Content-Type: application/json');

// DB接続（必要に応じて接続情報を変更）
$pdo = new PDO('mysql:host=localhost;dbname=your_db;charset=utf8', 'your_user', 'your_pass');

// POSTデータ取得
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['product_id'], $data['name'], $data['stock'], $data['price'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

// ユーザーID（例としてセッションで仮置き）
$user_id = $_SESSION['user_id'] ?? 1;

$stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, product_name, quantity, price) 
                       VALUES (?, ?, ?, ?, ?)");

$result = $stmt->execute([
    $user_id,
    $data['product_id'],
    $data['name'],
    1, // 数量は固定で1
    $data['price']
]);

echo json_encode(['status' => $result ? 'success' : 'error']);
