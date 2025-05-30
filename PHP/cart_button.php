<?php
// カートボタン表示用の関数
function displayCartButton($product_id, $product_name, $stock, $price, $show_quantity = true) {
    // ログインチェック
    if (!isset($_SESSION['user_id'])) {
        echo '<button class="cart-btn login-required" onclick="requireLogin()">カートに追加</button>';
        return;
    }
    
    // 在庫チェック
    if ($stock <= 0) {
        echo '<button class="cart-btn out-of-stock" disabled>在庫切れ</button>';
        return;
    }
    
    echo '<div class="cart-button-container">';
    if ($show_quantity) {
        echo '<div class="quantity-selector">';
        echo '<button type="button" class="quantity-btn minus" onclick="changeQuantity(' . $product_id . ', -1)">-</button>';
        echo '<input type="number" id="quantity-' . $product_id . '" class="quantity-input" value="1" min="1" max="' . $stock . '">';
        echo '<button type="button" class="quantity-btn plus" onclick="changeQuantity(' . $product_id . ', 1)">+</button>';
        echo '</div>';
    }
    echo '<button class="cart-btn add-to-cart" onclick="addToCart(' . $product_id . ', \'' . htmlspecialchars($product_name, ENT_QUOTES) . '\', ' . $price . ', ' . ($show_quantity ? 'true' : 'false') . ')">';
    echo '<span class="cart-icon">🛒</span>';
    echo '<span class="cart-text">カートに追加</span>';
    echo '</button>';
    echo '</div>';
}

// カートボタン用のCSS
function getCartButtonCSS() {
    return '
    <style>
        .cart-button-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
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
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }
        
        .quantity-btn:hover:not(:disabled) {
            background: #e9ecef;
        }
        
        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .quantity-input {
            border: none;
            width: 50px;
            height: 30px;
            text-align: center;
            font-size: 14px;
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
        }
        
        .quantity-input:focus {
            outline: none;
            background: #f8f9fa;
        }
        
        .cart-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .cart-btn:hover:not(:disabled) {
            background: #0056b3;
            transform: translateY(-1px);
        }
        
        .cart-btn.login-required {
            background: #6c757d;
        }
        
        .cart-btn.login-required:hover {
            background: #545b62;
        }
        
        .cart-btn.out-of-stock {
            background: #dc3545;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .cart-btn.adding {
            background: #28a745;
            pointer-events: none;
        }
        
        .cart-icon {
            font-size: 16px;
        }
        
        .cart-text {
            font-weight: 500;
        }
        
        /* 商品カード内でのスタイル調整 */
        .product-card .cart-button-container {
            margin-top: auto;
            padding-top: 10px;
        }
        
        .product-card .cart-btn {
            width: 100%;
            justify-content: center;
            padding: 10px 16px;
        }
        
        .product-card .quantity-selector {
            width: 100%;
            margin-bottom: 8px;
        }
        
        .product-card .quantity-input {
            flex: 1;
        }
        
        /* レスポンシブ対応 */
        @media (max-width: 768px) {
            .cart-button-container {
                flex-direction: column;
                gap: 8px;
            }
            
            .quantity-selector {
                width: 100%;
            }
            
            .cart-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>';
}

// カートボタン用のJavaScript
function getCartButtonJS() {
    return '
    <script>
        // 数量変更
        function changeQuantity(productId, change) {
            const input = document.getElementById("quantity-" + productId);
            if (!input) return;
            
            let newValue = parseInt(input.value) + change;
            const min = parseInt(input.min);
            const max = parseInt(input.max);
            
            if (newValue < min) newValue = min;
            if (newValue > max) newValue = max;
            
            input.value = newValue;
            
            // ボタンの状態更新
            updateQuantityButtons(productId);
        }
        
        // 数量ボタンの状態更新
        function updateQuantityButtons(productId) {
            const input = document.getElementById("quantity-" + productId);
            if (!input) return;
            
            const container = input.closest(".quantity-selector");
            const minusBtn = container.querySelector(".quantity-btn.minus");
            const plusBtn = container.querySelector(".quantity-btn.plus");
            
            const value = parseInt(input.value);
            const min = parseInt(input.min);
            const max = parseInt(input.max);
            
            minusBtn.disabled = value <= min;
            plusBtn.disabled = value >= max;
        }
        
        // カートに追加
        function addToCart(productId, productName, price, hasQuantity) {
            let quantity = 1;
            
            if (hasQuantity) {
                const quantityInput = document.getElementById("quantity-" + productId);
                if (quantityInput) {
                    quantity = parseInt(quantityInput.value);
                }
            }
            
            const button = event.target.closest(".cart-btn");
            const originalText = button.innerHTML;
            
            // ボタンの状態変更
            button.classList.add("adding");
            button.innerHTML = "<span class=\"cart-icon\">⏳</span><span class=\"cart-text\">追加中...</span>";
            
            // Ajax リクエスト
            fetch("add_to_cart.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 成功時
                    button.innerHTML = "<span class=\"cart-icon\">✓</span><span class=\"cart-text\">追加完了</span>";
                    
                    // 成功メッセージ表示
                    showNotification(`${productName} をカートに追加しました`, "success");
                    
                    // 2秒後に元に戻す
                    setTimeout(() => {
                        button.classList.remove("adding");
                        button.innerHTML = originalText;
                    }, 2000);
                } else {
                    // エラー時
                    button.classList.remove("adding");
                    button.innerHTML = originalText;
                    showNotification(data.message || "カートへの追加に失敗しました", "error");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                button.classList.remove("adding");
                button.innerHTML = originalText;
                showNotification("カートへの追加に失敗しました", "error");
            });
        }
        
        // ログインが必要な場合
        function requireLogin() {
            if (confirm("カートに商品を追加するにはログインが必要です。ログインページに移動しますか？")) {
                window.location.href = "login.php";
            }
        }
        
        // 通知表示
        function showNotification(message, type) {
            // 既存の通知があれば削除
            const existingNotification = document.querySelector(".notification");
            if (existingNotification) {
                existingNotification.remove();
            }
            
            const notification = document.createElement("div");
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.remove()">×</button>
            `;
            
            document.body.appendChild(notification);
            
            // 3秒後に自動削除
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 3000);
        }
        
        // 数量入力の直接変更対応
        document.addEventListener("DOMContentLoaded", function() {
            const quantityInputs = document.querySelectorAll(".quantity-input");
            quantityInputs.forEach(input => {
                input.addEventListener("input", function() {
                    const productId = this.id.replace("quantity-", "");
                    updateQuantityButtons(productId);
                });
                
                // 初期状態のボタン更新
                const productId = input.id.replace("quantity-", "");
                updateQuantityButtons(productId);
            });
        });
    </script>
    
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 250px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        }
        
        .notification.success {
            background: #28a745;
        }
        
        .notification.error {
            background: #dc3545;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>';
}
?>