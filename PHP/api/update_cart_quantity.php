<?php
session_start();
require_once '../DbManager.php';

$cart_id = $_POST['cart_id'];
$quantity = $_POST['quantity'];

$pdo = getDb();

// 単価取得
$stmt = $pdo->prepare("SELECT product_id FROM cart_items WHERE id = ?");
$stmt->execute([$cart_id]);
$product_id = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$price = $stmt->fetchColumn();

// 数量更新
$stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
$stmt->execute([$quantity, $cart_id]);

echo json_encode(['success' => true, 'price' => $price]);
