<?php
session_start();
require_once '../DbManager.php';

$cart_id = $_POST['cart_id'];

$pdo = getDb();
$stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ?");
$stmt->execute([$cart_id]);

echo json_encode(['success' => true]);
