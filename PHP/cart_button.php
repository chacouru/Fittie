<?php
// 必要なファイルを読み込み
require_once __DIR__ . '/DbManager.php';
require_once __DIR__ . '/Encode.php';

// セッションが開始されていない場合は開始
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ログインチェック関数
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// カートに商品を追加する処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    header('Content-Type: application/json');
    
    // ログインチェック
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'ログインが必要です']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']) ?: 1;
    
    try {
        // データベース接続
        $pdo = getDb();
        
        // 商品の在庫チェック
        $stmt = $pdo->prepare("SELECT stock, name FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => '商品が見つかりません']);
            exit;
        }
        
        if ($product['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => '在庫が不足しています']);
            exit;
        }
        
        // 既にカートに同じ商品があるかチェック
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_item) {
            // 既存の商品の数量を更新
            $new_quantity = $existing_item['quantity'] + $quantity;
            if ($new_quantity > $product['stock']) {
                echo json_encode(['success' => false, 'message' => '在庫を超える数量は追加できません']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $existing_item['id']]);
        } else {
            // 新しい商品をカートに追加
            $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }
        
        // カート内の商品数を取得
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart_total = $stmt->fetch(PDO::FETCH_ASSOC)['total_items'] ?: 0;
        
        echo json_encode([
            'success' => true, 
            'message' => 'カートに追加しました',
            'cart_total' => $cart_total
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'エラーが発生しました']);
    }
    exit;
}

// カートボタンを表示する関数
function displayCartButton($product_id, $product_name, $stock, $price) {
    $is_logged_in = isLoggedIn();
    $button_class = $stock > 0 ? 'cart-btn-active' : 'cart-btn-disabled';
    $button_text = $stock > 0 ? 'カートに入れる' : '在庫切れ';
    $disabled = $stock <= 0 ? 'disabled' : '';
    
    echo '
    <div class="cart-section">
        <div class="quantity-selector" ' . ($stock <= 0 ? 'style="display:none;"' : '') . '>
            <label for="quantity-' . $product_id . '">数量:</label>
            <select id="quantity-' . $product_id . '" class="quantity-select">
                ' . generateQuantityOptions($stock) . '
            </select>
        </div>
        
        <button 
            class="cart-button ' . $button_class . '" 
            data-product-id="' . $product_id . '"
            data-product-name="' . e($product_name) . '"
            data-price="' . $price . '"
            ' . $disabled . '
            ' . (!$is_logged_in ? 'data-login-required="true"' : '') . '
        >
           
            <span class="button-text">' . $button_text . '</span>
        </button>
    </div>
    
    <!-- 成功/エラーメッセージ表示エリア -->
    <div id="cart-message-' . $product_id . '" class="cart-message" style="display:none;"></div>
    ';
}

// 数量選択オプションを生成
function generateQuantityOptions($stock, $max = 10) {
    $options = '';
    $limit = min($stock, $max);
    for ($i = 1; $i <= $limit; $i++) {
        $options .= '<option value="' . $i . '">' . $i . '</option>';
    }
    return $options;
}
?>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // カートボタンのクリックイベント
    document.querySelectorAll('.cart-button').forEach(button => {
        button.addEventListener('click', function() {
            // ログインチェック
            if (this.hasAttribute('data-login-required')) {
                alert('カートに商品を追加するにはログインが必要です。');
                // ログインページにリダイレクト（適切なURLに変更してください）
                window.location.href = 'login.php';
                return;
            }
            
            // 無効化されているボタンのクリックを無視
            if (this.disabled) return;
            
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const quantitySelect = document.getElementById('quantity-' + productId);
            const quantity = quantitySelect ? quantitySelect.value : 1;
            const messageDiv = document.getElementById('cart-message-' + productId);
            
            // ローディング状態にする
            this.classList.add('loading');
            this.disabled = true;
            
            // AJAX でカートに追加
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
                // ローディング状態を解除
                this.classList.remove('loading');
                this.disabled = false;
                
                // メッセージを表示
                messageDiv.textContent = data.message;
                messageDiv.className = 'cart-message ' + (data.success ? 'success' : 'error');
                messageDiv.style.display = 'block';
                
                if (data.success) {
                    // カート数量を更新（ヘッダーなどにカート数を表示している場合）
                    updateCartCount(data.cart_total);
                    
                    // 成功時のアニメーション効果
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                }
                
                // メッセージを3秒後に非表示
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                this.classList.remove('loading');
                this.disabled = false;
                
                messageDiv.textContent = 'エラーが発生しました。もう一度お試しください。';
                messageDiv.className = 'cart-message error';
                messageDiv.style.display = 'block';
                
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 3000);
            });
        });
    });
});

// カート数量を更新する関数（ヘッダーのカートアイコンなどで使用）
function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count, #cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
        if (count > 0) {
            element.style.display = 'inline';
        }
    });
}
</script>

<?php
// 使用例：商品詳細ページや商品一覧ページで使用
// require_once 'cart_button.php';
// displayCartButton($product['id'], $product['name'], $product['stock'], $product['price']);
?>