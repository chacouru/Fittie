<?php
/**
 * カート操作API
 * 数量更新、商品削除を処理
 */
require_once __DIR__ . '/login_function/functions.php';
require_once __DIR__ . '/DbManager.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $user_id = check_login();
    $pdo = getDb();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $cart_id = $input['cart_id'] ?? 0;
    
    switch ($action) {
        case 'update_quantity':
        case 'increase':
        case 'decrease':
            $quantity = $input['quantity'] ?? 1;
            
            if ($action === 'increase') {
                // 現在の数量を取得して+1
                $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE id = :cart_id AND user_id = :user_id");
                $stmt->execute([':cart_id' => $cart_id, ':user_id' => $user_id]);
                $current = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($current) {
                    $quantity = $current['quantity'] + 1;
                }
            } elseif ($action === 'decrease') {
                // 現在の数量を取得して-1（最小1）
                $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE id = :cart_id AND user_id = :user_id");
                $stmt->execute([':cart_id' => $cart_id, ':user_id' => $user_id]);
                $current = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($current) {
                    $quantity = max(1, $current['quantity'] - 1);
                }
            }
            
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = :quantity WHERE id = :cart_id AND user_id = :user_id");
            $result = $stmt->execute([
                ':quantity' => $quantity,
                ':cart_id' => $cart_id,
                ':user_id' => $user_id
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'new_quantity' => $quantity]);
            } else {
                throw new Exception('数量の更新に失敗しました');
            }
            break;
            
        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = :cart_id AND user_id = :user_id");
            $result = $stmt->execute([
                ':cart_id' => $cart_id,
                ':user_id' => $user_id
            ]);
            
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('商品の削除に失敗しました');
            }
            break;
            
        default:
            throw new Exception('不正なアクションです');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>