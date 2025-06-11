<?php
/**
 * カートボタンコンポーネント
 * ECサイトの商品カード用のカートに入れるボタン機能
 */

// データベース接続
require_once 'db_connect.php';

/**
 * カートに商品を追加する処理
 */
function addToCart($user_id, $product_id, $quantity = 1) {
    global $pdo;
    
    try {
        
        // 既にカートに同じ商品があるかチェック
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_item) {
            // 既存アイテムの数量を更新
            $new_quantity = $existing_item['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $existing_item['id']]);
        } else {
            // 新しいアイテムを追加
            $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }
        
        return ['success' => true, 'message' => 'カートに追加しました'];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'エラーが発生しました: ' . $e->getMessage()];
    }
}

/**
 * カートボタンを表示する関数
 * 
 * @param int $product_id 商品ID
 * @param string $product_name 商品名
 * @param int $stock 在庫数
 * @param float $price 価格
 * @param int $user_id ユーザーID（セッションから取得する場合は省略可）
 */
function displayCartButton($product_id, $product_name, $stock, $price, $user_id = null) {
    // セッションからユーザーIDを取得（ログイン状態の確認）
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if ($user_id === null) {
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    // 在庫状況の確認
    $is_in_stock = $stock > 0;
    $is_logged_in = $user_id !== null;
    
    // Ajax処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
        if (!$is_logged_in) {
            echo json_encode(['success' => false, 'message' => 'ログインが必要です']);
            exit;
        }
        
        if (!$is_in_stock) {
            echo json_encode(['success' => false, 'message' => '在庫がありません']);
            exit;
        }
        
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $result = addToCart($user_id, (int)$_POST['product_id'], $quantity);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    // HTML & CSS & JavaScript を出力
    ?>
    <div class="cart-button-container" data-product-id="<?php echo htmlspecialchars($product_id); ?>">
        <style>
        .cart-button-container {
            margin: 10px 0;
        }
        
        .cart-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .quantity-btn {
            background: #f8f9fa;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        
        .quantity-btn:hover:not(:disabled) {
            background: #e9ecef;
        }
        
        .quantity-btn:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }
        
        .quantity-input {
            border: none;
            padding: 8px;
            width: 50px;
            text-align: center;
            font-size: 14px;
        }
        
        .price-display {
            font-weight: bold;
            color: #e74c3c;
            font-size: 16px;
        }
        
        .add-to-cart-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.2s;
            width: 100%;
        }
        
        .add-to-cart-btn:hover:not(:disabled) {
            background: #0056b3;
            transform: translateY(-1px);
        }
        
        .add-to-cart-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .cart-message {
            margin-top: 10px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            display: none;
        }
        
        .cart-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .cart-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .stock-info {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .out-of-stock {
            color: #dc3545;
            font-weight: bold;
        }
        </style>
        
        <?php if ($is_in_stock): ?>
            <div class="cart-controls">
                <div class="quantity-selector">
                    <button type="button" class="quantity-btn" onclick="changeQuantity(<?php echo $product_id; ?>, -1)">-</button>
                    <input type="number" class="quantity-input" id="quantity-<?php echo $product_id; ?>" value="1" min="1" max="<?php echo $stock; ?>" readonly>
                    <button type="button" class="quantity-btn" onclick="changeQuantity(<?php echo $product_id; ?>, 1)">+</button>
                </div>
                <div class="price-display" id="total-price-<?php echo $product_id; ?>">
                    ¥<?php echo number_format($price); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <button type="button" 
                class="add-to-cart-btn" 
                id="cart-btn-<?php echo $product_id; ?>"
                onclick="addToCart(<?php echo $product_id; ?>, '<?php echo htmlspecialchars($product_name); ?>', <?php echo $price; ?>)"
                <?php echo !$is_in_stock || !$is_logged_in ? 'disabled' : ''; ?>>
            <?php 
            if (!$is_logged_in) {
                echo 'ログインしてください';
            } elseif (!$is_in_stock) {
                echo '売り切れ';
            } else {
                echo 'カートに入れる';
            }
            ?>
        </button>
        
        <div class="stock-info">
            <?php if ($is_in_stock): ?>
                残り<?php echo $stock; ?>個
            <?php else: ?>
                <span class="out-of-stock">在庫切れ</span>
            <?php endif; ?>
        </div>
        
        <div class="cart-message" id="cart-message-<?php echo $product_id; ?>"></div>
    </div>
    
    <script>
    // 数量変更機能
    function changeQuantity(productId, change) {
        const quantityInput = document.getElementById('quantity-' + productId);
        const currentQuantity = parseInt(quantityInput.value);
        const newQuantity = currentQuantity + change;
        const maxStock = parseInt(quantityInput.getAttribute('max'));
        
        if (newQuantity >= 1 && newQuantity <= maxStock) {
            quantityInput.value = newQuantity;
            updateTotalPrice(productId, <?php echo $price; ?>);
        }
    }
    
    // 合計価格更新
    function updateTotalPrice(productId, unitPrice) {
        const quantity = parseInt(document.getElementById('quantity-' + productId).value);
        const totalPrice = unitPrice * quantity;
        document.getElementById('total-price-' + productId).textContent = '¥' + totalPrice.toLocaleString();
    }
    
    // カートに追加
    function addToCart(productId, productName, price) {
        const quantityInput = document.getElementById('quantity-' + productId);
        const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
        const button = document.getElementById('cart-btn-' + productId);
        const messageDiv = document.getElementById('cart-message-' + productId);
        
        // ボタンを無効化
        button.disabled = true;
        button.textContent = '追加中...';
        
        // Ajax リクエスト
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // メッセージ表示
            messageDiv.textContent = data.message;
            messageDiv.className = 'cart-message ' + (data.success ? 'success' : 'error');
            messageDiv.style.display = 'block';
            
            // 成功時の処理
            if (data.success) {
                // カートアイコンの数量更新などの処理をここに追加
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
            }
            
            // 3秒後にメッセージを非表示
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 3000);
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.textContent = 'エラーが発生しました';
            messageDiv.className = 'cart-message error';
            messageDiv.style.display = 'block';
        })
        .finally(() => {
            // ボタンを再有効化
            button.disabled = false;
            button.textContent = 'カートに入れる';
        });
    }
    </script>
    <?php
}

/**
 * カート内のアイテム数を取得する関数
 */
function getCartItemCount($user_id) {
    global $pdo;
    
    try {
        
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total_items'] ? (int)$result['total_items'] : 0;
        
    } catch (PDOException $e) {
        return 0;
    }
}
?>