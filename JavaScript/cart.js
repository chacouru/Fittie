/**
 * カートページのJavaScript
 * APIからカート情報を取得して表示、操作を処理
 */

class CartManager {
    constructor() {
        this.cartItems = [];
        this.init();
    }

    async init() {
        await this.loadCart();
        this.bindEvents();
    }

    async loadCart() {
        try {
            const response = await fetch('cart_api.php');
            const data = await response.json();
            
            if (data.success) {
                this.cartItems = data.items;
                this.renderCart();
            } else {
                this.showError('カート情報の取得に失敗しました');
            }
        } catch (error) {
            console.error('Error loading cart:', error);
            this.showError('カート情報の取得中にエラーが発生しました');
        }
    }

    renderCart() {
        const loadingEl = document.getElementById('loading');
        const emptyCartEl = document.getElementById('empty-cart');
        const cartItemsEl = document.getElementById('cart-items');
        const cartSummaryEl = document.getElementById('cart-summary');
        const cartTitleEl = document.getElementById('cart-title');

        // ローディング非表示
        loadingEl.style.display = 'none';

        if (this.cartItems.length === 0) {
            // 空のカート
            emptyCartEl.style.display = 'block';
            cartItemsEl.style.display = 'none';
            cartSummaryEl.style.display = 'none';
            cartTitleEl.textContent = 'カートに入っている商品：0点';
        } else {
            // カート商品表示
            emptyCartEl.style.display = 'none';
            cartItemsEl.style.display = 'block';
            cartSummaryEl.style.display = 'block';
            
            const totalItems = this.cartItems.reduce((sum, item) => sum + parseInt(item.quantity), 0);
            cartTitleEl.textContent = `カートに入っている商品：${totalItems}点`;
            
            this.renderCartItems();
            this.updateTotal();
        }
    }

    renderCartItems() {
        const cartItemsEl = document.getElementById('cart-items');
        cartItemsEl.innerHTML = '';

        this.cartItems.forEach(item => {
            const itemEl = document.createElement('div');
            itemEl.className = 'cart-item';
            itemEl.dataset.cartId = item.id;
            itemEl.dataset.stock = item.stock;

            itemEl.innerHTML = `
                <div class="item-image">
                    <img src="${item.image_path}" 
                         alt="${this.escapeHtml(item.name)}" 
                         onerror="this.src='img/products/no-image.png';">
                </div>
                
                <div class="item-details">
                    <div class="item-brand">
                        ${this.escapeHtml(item.brand_name || 'ブランド名なし')}
                    </div>
                    <div class="item-name">
                        ${this.escapeHtml(item.name)}
                    </div>
                    <div class="item-size">
                        サイズ：FREE
                    </div>
                    <div class="item-price">
                        ¥${this.formatPrice(item.price)}
                    </div>
                </div>
                
                <div class="item-actions">
                    <button class="delete-btn" data-cart-id="${item.id}">削除</button>
                    <div class="quantity-controls">
                        <span class="quantity-label">数量</span>
                        <button class="quantity-btn decrease" data-cart-id="${item.id}">−</button>
                        <span class="quantity-display">${item.quantity}</span>
                        <button class="quantity-btn increase" data-cart-id="${item.id}">＋</button>
                    </div>
                </div>
            `;

            cartItemsEl.appendChild(itemEl);
        });
    }

    bindEvents() {
        // イベント委譲を使用
        document.getElementById('cart-items').addEventListener('click', (e) => {
            const cartId = e.target.dataset.cartId;
            if (!cartId) return;

            if (e.target.classList.contains('delete-btn')) {
                this.deleteItem(cartId);
            } else if (e.target.classList.contains('increase')) {
                this.updateQuantity(cartId, 'increase');
            } else if (e.target.classList.contains('decrease')) {
                this.updateQuantity(cartId, 'decrease');
            }
        });

        // チェックアウトボタン
        document.getElementById('checkout-btn').addEventListener('click', () => {
            this.checkout();
        });
    }

    async updateQuantity(cartId, action) {
        try {
            const response = await fetch('cart_update_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    cart_id: cartId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // ローカルデータを更新
                const item = this.cartItems.find(item => item.id == cartId);
                if (item) {
                    item.quantity = data.new_quantity;
                    item.subtotal = item.price * item.quantity;
                }
                
                // 表示を更新
                this.updateItemDisplay(cartId, data.new_quantity);
                this.updateTotal();
                this.updateCartTitle();
            } else {
                this.showError('数量の更新に失敗しました');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            this.showError('数量の更新中にエラーが発生しました');
        }
    }

    async deleteItem(cartId) {
        if (!confirm('この商品をカートから削除しますか？')) {
            return;
        }

        try {
            const response = await fetch('cart_update_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete',
                    cart_id: cartId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // ローカルデータから削除
                this.cartItems = this.cartItems.filter(item => item.id != cartId);
                
                // 表示を再描画
                this.renderCart();
            } else {
                this.showError('商品の削除に失敗しました');
            }
        } catch (error) {
            console.error('Error deleting item:', error);
            this.showError('商品の削除中にエラーが発生しました');
        }
    }

    updateItemDisplay(cartId, newQuantity) {
        const itemEl = document.querySelector(`[data-cart-id="${cartId}"]`);
        if (itemEl) {
            const quantityDisplay = itemEl.querySelector('.quantity-display');
            quantityDisplay.textContent = newQuantity;
        }
    }

    updateTotal() {
        const total = this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        document.getElementById('total-amount').textContent = `¥${this.formatPrice(total)}`;
    }

    updateCartTitle() {
        const totalItems = this.cartItems.reduce((sum, item) => sum + parseInt(item.quantity), 0);
        document.getElementById('cart-title').textContent = `カートに入っている商品：${totalItems}点`;
    }

    checkout() {
        if (this.cartItems.length === 0) {
            alert('カートに商品がありません');
            return;
        }
        
        // チェックアウトページに移動
        window.location.href = 'checkout.php';
    }

    formatPrice(price) {
        return parseInt(price).toLocaleString();
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showError(message) {
        alert(message);
    }
}

// ページ読み込み時に初期化
document.addEventListener('DOMContentLoaded', () => {
    new CartManager();
});