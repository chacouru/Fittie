<?php
// このファイルは一時的なデバッグ用です
require_once __DIR__ . '/login_function/functions.php';
require_once 'db_connect.php';

echo "<h2>カートデバッグ情報</h2>";

// 1. ログイン状態確認
try {
    $user_id = check_login();
    echo "<p>✓ ログイン確認: ユーザーID = " . $user_id . "</p>";
} catch (Exception $e) {
    echo "<p>✗ ログインエラー: " . $e->getMessage() . "</p>";
    exit;
}

// 2. データベース接続確認


// 3. cart_itemsテーブル確認
try {
    $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>cart_itemsテーブル（ユーザーID: {$user_id}）</h3>";
    if (empty($cart_items)) {
        echo "<p>カートアイテムが見つかりません</p>";
    } else {
        echo "<pre>" . print_r($cart_items, true) . "</pre>";
    }
} catch (PDOException $e) {
    echo "<p>✗ cart_items取得エラー: " . $e->getMessage() . "</p>";
}

// 4. 全cart_itemsテーブル確認（デバッグ用）
try {
    $stmt = $pdo->query("SELECT * FROM cart_items");
    $all_cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>全cart_itemsテーブル</h3>";
    if (empty($all_cart_items)) {
        echo "<p>cart_itemsテーブルにデータがありません</p>";
    } else {
        echo "<pre>" . print_r($all_cart_items, true) . "</pre>";
    }
} catch (PDOException $e) {
    echo "<p>✗ 全cart_items取得エラー: " . $e->getMessage() . "</p>";
}

// 5. JOINクエリ確認
try {
    $stmt = $pdo->prepare("
        SELECT 
            ci.id,
            ci.user_id,
            ci.product_id,
            ci.quantity,
            p.name,
            p.price,
            p.image,
            p.is_on_sale,
            p.sale_price,
            b.name as brand_name
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE ci.user_id = ?
        ORDER BY ci.id DESC
    ");
    
    $stmt->execute([$user_id]);
    $joined_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>JOINクエリ結果（ユーザーID: {$user_id}）</h3>";
    if (empty($joined_items)) {
        echo "<p>JOINクエリでデータが見つかりません</p>";
    } else {
        echo "<pre>" . print_r($joined_items, true) . "</pre>";
    }
} catch (PDOException $e) {
    echo "<p>✗ JOINクエリエラー: " . $e->getMessage() . "</p>";
}

// 6. productsテーブルの該当商品確認
if (!empty($cart_items)) {
    foreach ($cart_items as $cart_item) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$cart_item['product_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h4>商品ID {$cart_item['product_id']} の詳細</h4>";
            if ($product) {
                echo "<pre>" . print_r($product, true) . "</pre>";
            } else {
                echo "<p>商品が見つかりません（削除済み？）</p>";
            }
        } catch (PDOException $e) {
            echo "<p>✗ 商品取得エラー: " . $e->getMessage() . "</p>";
        }
    }
}
?>