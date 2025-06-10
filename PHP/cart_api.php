<?php
require_once __DIR__ . '/login_function/functions.php';
require_once __DIR__ . '/db_connect.php'; 

// ログイン確認
$user_id = check_login();



header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'get_cart') {
            getCartItems($pdo, $user_id);
        }
        break;
    
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'update_quantity') {
            updateQuantity($pdo, $user_id, $input);
        } elseif ($action === 'remove_item') {
            removeItem($pdo, $user_id, $input);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function getCartItems($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                ci.id,
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
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = 0;
        $formatted_items = [];
        
        foreach ($items as $item) {
            $price = $item['is_on_sale'] ? $item['sale_price'] : $item['price'];
            $subtotal = $price * $item['quantity'];
            $total += $subtotal;
            
            // 画像パスを適切に設定
            $image_path = '';
            if ($item['image']) {
                $brand_name = $item['brand_name'] ?: 'default';
                $image_path = "img/products/{$brand_name}/{$item['image']}";
                
                // ファイルが存在しない場合はno-imageを使用
                if (!file_exists(__DIR__ . '/' . $image_path)) {
                    $image_path = 'img/products/no-image.png';
                }
            } else {
                $image_path = 'img/products/no-image.png';
            }
            
            $formatted_items[] = [
                'id' => $item['id'],
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'brand_name' => $item['brand_name'] ?: 'ブランド未設定',
                'price' => $price,
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
                'image' => $image_path,
                'is_on_sale' => $item['is_on_sale']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'items' => $formatted_items,
            'total' => $total,
            'count' => count($items)
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'カート情報の取得に失敗しました']);
    }
}

function updateQuantity($pdo, $user_id, $input) {
    try {
        $cart_item_id = $input['cart_item_id'];
        $quantity = max(1, intval($input['quantity'])); // 最小値は1
        
        $stmt = $pdo->prepare("
            UPDATE cart_items 
            SET quantity = ? 
            WHERE id = ? AND user_id = ?
        ");
        
        $result = $stmt->execute([$quantity, $cart_item_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => '数量を更新しました']);
        } else {
            echo json_encode(['success' => false, 'message' => '更新に失敗しました']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => '数量の更新に失敗しました']);
    }
}

function removeItem($pdo, $user_id, $input) {
    try {
        $cart_item_id = $input['cart_item_id'];
        
        $stmt = $pdo->prepare("
            DELETE FROM cart_items 
            WHERE id = ? AND user_id = ?
        ");
        
        $result = $stmt->execute([$cart_item_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => '商品を削除しました']);
        } else {
            echo json_encode(['success' => false, 'message' => '削除に失敗しました']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => '商品の削除に失敗しました']);
    }
}
?>