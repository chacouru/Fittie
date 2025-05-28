'use strict';

window.addEventListener('DOMContentLoaded', async function () {
    const container = document.getElementById('cart_items_container');
    const cartTitle = document.getElementById('cart_title');
    const totalPrice = document.querySelector('.total_price');
    let cartItems = [];

    try {
        const res = await fetch('../PHP/get_cart_items.php');
        cartItems = await res.json();

        renderCartItems();
        updateCartTitle();
        updateTotal();
    } catch (err) {
        container.innerHTML = '<p>カートの読み込みに失敗しました。</p>';
        console.error(err);
    }

    function renderCartItems() {
        container.innerHTML = '';
        cartItems.forEach(item => {
            const cartItemHTML = `
                <div class="cart_item">
                    <div class="item_image"><img src="../img/products/${item.brand_name}/${item.image}" alt="${item.name}">
                    </div>
                    <div class="item_details">
                        <div class="item_name">${item.name}</div>
                        <div class="item_price">¥${item.price.toLocaleString()}</div>
                        <div class="quantity_controls">
                            <span class="quantity_label">数量</span>
                            <button class="quantity_btn" data-id="${item.id}" data-action="decrease">-</button>
                            <input type="number" class="quantity-input" value="${item.quantity}" id="qty${item.id}" readonly>
                            <button class="quantity_btn" data-id="${item.id}" data-action="increase">+</button>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += cartItemHTML;
        });

        // 数量ボタンにイベントを追加
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
    }

    function updateCartTitle() {
        const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
        cartTitle.textContent = `カートに入っている商品：${totalItems}点`;
    }

    function updateTotal() {
        const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        totalPrice.textContent = `¥${total.toLocaleString()}`;
    }

    function increaseQuantity(itemId) {
        const item = cartItems.find(item => item.id === itemId);
        if (item) {
            item.quantity++;
            document.getElementById('qty' + itemId).value = item.quantity;
            updateCartTitle();
            updateTotal();
            // TODO: 数量変更をサーバーに送信するならここでfetch POST
        }
    }

    function decreaseQuantity(itemId) {
        const item = cartItems.find(item => item.id === itemId);
        if (item && item.quantity > 1) {
            item.quantity--;
            document.getElementById('qty' + itemId).value = item.quantity;
            updateCartTitle();
            updateTotal();
            // TODO: 数量変更をサーバーに送信するならここでfetch POST
        }
    }
});
