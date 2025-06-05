<?php
// update_cart_quantity.php
/**
 * カート内商品の数量を更新するAPI
 */
require_once __DIR__ . '/../login_function/functions.php';
require_once __DIR__ . '/../DbManager.php';

$user_id = check_login();
$pdo = getDb();

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $item_id = $input['item_id'];
    $quantity = $input['quantity'];

    if ($quantity < 1) {
        throw new Exception('数量は1以上である必要があります');
    }

    $sql = "UPDATE cart_items SET quantity = :quantity 
            WHERE id = :item_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('数量の更新に失敗しました');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

// ============================================
// remove_cart_item.php
/**
 * カートから商品を削除するAPI
 */
require_once __DIR__ . '/../login_function/functions.php';
require_once __DIR__ . '/../DbManager.php';

$user_id = check_login();
$pdo = getDb();

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $item_id = $input['item_id'];

    $sql = "DELETE FROM cart_items WHERE id = :item_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('商品の削除に失敗しました');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}