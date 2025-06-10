<?php
require_once __DIR__ . '/login_function/functions.php';

// ログイン確認
$user_id = check_login();

// デバッグ: ログイン確認結果
error_log("Debug: Login check result - User ID: " . ($user_id ? $user_id : 'NULL'));

// データベース接続
try {
    $pdo = new PDO('mysql:host=localhost;dbname=fitty;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Debug: Database connection successful");
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'データベース接続エラー',
        'debug_message' => $e->getMessage()
    ]);
    exit;
}

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
        // デバッグ: ユーザーIDを確認
        error_log("Debug: User ID = " . $user_id);
        
        // まず、cart_itemsテーブルにデータがあるかを確認
        $debug_stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ?");
        $debug_stmt->execute([$user_id]);
        $debug_cart = $debug_stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Debug: Cart items found: " . print_r($debug_cart, true));
        
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
        
        // デバッグ: 取得した商品情報を確認
        error_log("Debug: Items with product info: " . print_r($items, true));
        
        $total = 0;
        $formatted_items = [];
        
        foreach ($items as $item) {
            $price = $item['is_on_sale'] ? $item['sale_price'] : $item['price'];
            $subtotal = $price * $item['quantity'];
            $total += $subtotal;
            
            $formatted_items[] = [
                'id' => $item['id'],
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'brand_name' => $item['brand_name'] ?: 'ブランド未設定',
                'price' => $price,
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
                'image' => $item['image'],
                'is_on_sale' => $item['is_on_sale']
            ];
        }
        
        // デバッグ: 最終的なレスポンスを確認
        $response = [
            'success' => true,
            'items' => $formatted_items,
            'total' => $total,
            'count' => count($items),
            'debug_user_id' => $user_id,
            'debug_raw_items' => $debug_cart
        ];
        error_log("Debug: Final response: " . print_r($response, true));
        
        echo json_encode($response);
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'error' => 'カート情報の取得に失敗しました',
            'debug_message' => $e->getMessage()
        ]);
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