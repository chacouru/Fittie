'use strict';

window.addEventListener('DOMContentLoaded', async function () {
    const container = document.getElementById('cart_items_container');
    const cartTitle = document.getElementById('cart_title');
    const totalPrice = document.querySelector('.total_price');
    const checkoutBtn = document.querySelector('.checkout_btn');
    let cartItems = [];

    // カートアイテムを読み込み
    await loadCartItems();

    // レジに進むボタンのイベントリスナー
    checkoutBtn.addEventListener('click', handleCheckout);

    async function loadCartItems() {
        try {
            const res = await fetch('./api/get_cart_items.php');
            
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            
            cartItems = await res.json();
            console.log('Cart items loaded:', cartItems);

            renderCartItems();
            updateCartTitle();
            updateTotal();
        } catch (err) {
            container.innerHTML = '<div class="empty_cart"><p>カートの読み込みに失敗しました。</p></div>';
            console.error('Cart loading error:', err);
        }
    }

    function renderCartItems() {
        container.innerHTML = '';
        
        if (cartItems.length === 0) {
            container.innerHTML = `
                <div class="empty_cart">
                    <p>カートに商品がありません。</p>
                    <a href="./toppage.php" class="continue_shopping">ショッピングを続ける</a>
                </div>
            `;
            checkoutBtn.disabled = true;
            return;
        }
        
        cartItems.forEach(item => {
            const imagePath = `../PHP/img/products/${item.brand_name}/${item.image}`;
            
            const cartItemHTML = `
                <div class="cart_item" data-item-id="${item.id}">
                    <div class="item_image">
                        <img src="${imagePath}" alt="${item.name}" 
                             onerror="this.src='../img/products/default.jpg';">
                    </div>
                    <div class="item_details">
                        <div class="item_brand">${item.brand_name}</div>
                        <div class="item_name">${item.name}</div>
                        <div class="item_size">サイズ: FREE</div>
                        <div class="item_price">¥${item.price.toLocaleString()}</div>
                        <div class="quantity_controls">
                            <span class="quantity_label">数量</span>
                            <button class="quantity_btn" data-id="${item.id}" data-action="decrease">-</button>
                            <input type="number" class="quantity-input" value="${item.quantity}" id="qty${item.id}" readonly>
                            <button class="quantity_btn" data-id="${item.id}" data-action="increase">+</button>
                        </div>
                    </div>
                    <button class="remove_btn" data-id="${item.id}">削除</button>
                </div>
            `;
            container.innerHTML += cartItemHTML;
        });

        // イベントリスナーを追加
        addEventListeners();
        checkoutBtn.disabled = false;
    }

    function addEventListeners() {
        // 数量変更ボタン
        document.querySelectorAll('.quantity_btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = parseInt(this.getAttribute('data-id'));
                const action = this.getAttribute('data-action');
                if (action === 'increase') {
                    increaseQuantity(id);
                } else {
                    decreaseQuantity(id);
                }
            });
        });

        // 削除ボタン
        document.querySelectorAll('.remove_btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = parseInt(this.getAttribute('data-id'));
                removeItem(id);
            });
        });
    }

    function updateCartTitle() {
        const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
        cartTitle.textContent = `カートに入っている商品：${totalItems}点`;
    }

    function updateTotal() {
        const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        totalPrice.textContent = `¥${total.toLocaleString()}`;
    }

    async function increaseQuantity(itemId) {
        const item = cartItems.find(item => item.id === itemId);
        if (item) {
            const newQuantity = item.quantity + 1;
            if (await updateQuantityOnServer(itemId, newQuantity)) {
                item.quantity = newQuantity;
                document.getElementById('qty' + itemId).value = newQuantity;
                updateCartTitle();
                updateTotal();
            }
        }
    }

    async function decreaseQuantity(itemId) {
        const item = cartItems.find(item => item.id === itemId);
        if (item && item.quantity > 1) {
            const newQuantity = item.quantity - 1;
            if (await updateQuantityOnServer(itemId, newQuantity)) {
                item.quantity = newQuantity;
                document.getElementById('qty' + itemId).value = newQuantity;
                updateCartTitle();
                updateTotal();
            }
        }
    }

    async function removeItem(itemId) {
        if (confirm('この商品をカートから削除しますか？')) {
            try {
                const response = await fetch('./api/remove_cart_item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ item_id: itemId })
                });

                if (response.ok) {
                    cartItems = cartItems.filter(item => item.id !== itemId);
                    renderCartItems();
                    updateCartTitle();
                    updateTotal();
                } else {
                    alert('削除に失敗しました。');
                }
            } catch (error) {
                console.error('Error removing item:', error);
                alert('削除に失敗しました。');
            }
        }
    }

    async function updateQuantityOnServer(itemId, quantity) {
        try {
            const response = await fetch('./api/update_cart_quantity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    item_id: itemId, 
                    quantity: quantity 
                })
            });

            return response.ok;
        } catch (error) {
            console.error('Error updating quantity:', error);
            return false;
        }
    }

    async function handleCheckout() {
        if (cartItems.length === 0) {
            alert('カートに商品がありません。');
            return;
        }

        checkoutBtn.disabled = true;
        checkoutBtn.textContent = '処理中...';

        try {
            // Stripe Checkoutセッションを作成
            const response = await fetch('./api/create_checkout_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ cart_items: cartItems })
            });

            const session = await response.json();

            if (session.error) {
                throw new Error(session.error);
            }

            // Stripe Checkoutにリダイレクト
            window.location.href = session.checkout_url;

        } catch (error) {
            console.error('Checkout error:', error);
            alert('決済の準備中にエラーが発生しました。もう一度お試しください。');
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = 'レジに進む';
        }
    }
});