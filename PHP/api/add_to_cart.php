<?php
session_start();
header('Content-Type: application/json');

// 必要なチェック
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ログインしてください']);
    exit;
}

// DB接続
$pdo = new PDO('mysql:host=localhost;dbname=fitty;charset=utf8mb4', 'ユーザー名', 'パスワード', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'product_id がありません']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$data['product_id'];

// すでに同じ商品があるかチェック
$stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    // 数量を1つ増やす
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
} else {
    // 新規追加
    $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, 1)");
    $stmt->execute([$user_id, $product_id]);
}

echo json_encode(['status' => 'success']);
