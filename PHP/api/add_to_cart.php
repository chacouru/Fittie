<?php
session_start();
require_once '../DbManager.php';
header('Content-Type: application/json');

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$quantity = 1; // 必要ならJSから受け取ってもOK

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => '商品IDが不正です']);
    exit;
}

try {
    $pdo = getDb();

    // 商品の存在＆在庫確認（在庫が必要ならここでチェック）
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => '商品が存在しません']);
        exit;
    }

    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => '在庫が足りません']);
        exit;
    }

    // すでに同じ商品がカートにあるか？
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // 数量を加算して更新
        $new_quantity = $existing['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $existing['id']]);
    } else {
        // 新規追加
        $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    file_put_contents('cart_error_log.txt', $e->getMessage(), FILE_APPEND); // ログ出力
    echo json_encode(['success' => false, 'message' => 'DBエラー']);
}

