document.addEventListener('DOMContentLoaded', function() {
    loadCartItems();
});

// カート商品を読み込み
async function loadCartItems() {
    try {
        const response = await fetch('cart_api.php?action=get_cart');
        const data = await response.json();
        
        if (data.success) {
            displayCartItems(data.items, data.total, data.count);
        } else {
            showError('カート情報の読み込みに失敗しました');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('通信エラーが発生しました');
    }
}

// カート商品を表示
function displayCartItems(items, total, count) {
    const container = document.getElementById('cart_items_container');
    const titleElement = document.getElementById('cart_title');
    const totalElement = document.querySelector('.total_price');
    
    // タイトル更新
    titleElement.textContent = `カートに入っている商品：${count}点`;
    
    // 合計金額更新
    totalElement.textContent = `¥${total.toLocaleString()}`;
    
    // カートが空の場合
    if (items.length === 0) {
        container.innerHTML = `
            <div class="empty_cart">
                <p>カートに商品が入っていません</p>
                <a href="toppage.php" class="continue_shopping">ショッピングを続ける</a>
            </div>
        `;
        return;
    }
    
    // 商品アイテム表示
    container.innerHTML = items.map(item => `
        <div class="cart_item" data-cart-id="${item.id}">
            <div class="item_image">
                <img src="${item.image}" alt="${item.name}" onerror="this.src='img/products/no-image.png'">
            </div>
            <div class="item_details">
                <div class="item_brand">${item.brand_name}</div>
                <div class="item_name">${item.name}</div>
                <div class="item_size">サイズ: FREE</div>
                <div class="item_price">¥${item.price.toLocaleString()}</div>
            </div>
            <div class="item_controls">
                <button class="remove_btn" onclick="removeItem(${item.id})">削除</button>
                <div class="quantity_controls">
                    <span class="quantity_label">数量</span>
                    <button class="qty_btn minus" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                    <input type="number" class="quantity_input" value="${item.quantity}" 
                           min="1" onchange="updateQuantity(${item.id}, this.value)">
                    <button class="qty_btn plus" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                </div>
            </div>
        </div>
    `).join('');
}

// 数量更新
async function updateQuantity(cartItemId, newQuantity) {
    if (newQuantity < 1) return;
    
    try {
        const response = await fetch('cart_api.php?action=update_quantity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_item_id: cartItemId,
                quantity: parseInt(newQuantity)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadCartItems(); // カート再読み込み
        } else {
            showError(data.message || '数量の更新に失敗しました');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('通信エラーが発生しました');
    }
}

// 商品削除
async function removeItem(cartItemId) {
    if (!confirm('この商品をカートから削除しますか？')) {
        return;
    }
    
    try {
        const response = await fetch('cart_api.php?action=remove_item', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_item_id: cartItemId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadCartItems(); // カート再読み込み
            showSuccess('商品を削除しました');
        } else {
            showError(data.message || '削除に失敗しました');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('通信エラーが発生しました');
    }
}

// エラーメッセージ表示
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-error';
    errorDiv.textContent = message;
    document.body.appendChild(errorDiv);
    
    setTimeout(() => {
        errorDiv.remove();
    }, 3000);
}

// 成功メッセージ表示
function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'alert alert-success';
    successDiv.textContent = message;
    document.body.appendChild(successDiv);
    
    setTimeout(() => {
        successDiv.remove();
    }, 3000);
}