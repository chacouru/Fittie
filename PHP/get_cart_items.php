<?php
/**
 * 目的：ログインユーザーのカート情報をDBから取得してJSONで返すAPI。
 * フロントエンドのJS（cart.jsなど）がこのPHPを fetch() して、JSONを受け取って表示する。 
 */
require_once __DIR__ . '/login_function/functions.php';
require_once __DIR__ . '/DbManager.php';

$user_id = check_login(); // ログイン確認してユーザーID取得
$pdo = getDb(); // ← ここでPDOインスタンスを取得する！

$sql = "SELECT
          ci.id,
          p.name,
          p.price,
          p.image,
          ci.quantity,
          b.name AS brand_name
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        JOIN brands b ON p.brand_id = b.id
        WHERE ci.user_id = :user_id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($items);
