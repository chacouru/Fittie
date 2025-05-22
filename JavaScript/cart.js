'usestrict';
       // カートデータの例（実際の実装では外部APIやローカルストレージから取得）
        const cartItems = [
            {
                id: 1,
                brand: "Nike",
                name: "エアマックス",
                size: "26.5cm",
                price: 12800,
                quantity: 1,
                image: ""
            },
            {
                id: 2,
                brand: "Adidas",
                name: "スタンスミス",
                size: "27.0cm", 
                price: 9800,
                quantity: 2,
                image: ""
            },
            {
                id: 3,
                brand: "Converse",
                name: "オールスター",
                size: "26.0cm",
                price: 6800,
                quantity: 1,
                image: ""
            }
        ];

        // ページ読み込み時にカートを表示
        window.addEventListener('DOMContentLoaded', function() {
            renderCartItems();
            updateCartTitle();
            updateTotal();
        });

        // カートアイテムを表示する関数
        function renderCartItems() {
            const container = document.getElementById('cart-items-container');
            container.innerHTML = '';

            cartItems.forEach(item => {
                const cartItemHTML = `
                    <div class="cart_item">
                        <div class="item_image"></div>
                        <div class="item_details">
                            <div class="item_brand">${item.brand}</div>
                            <div class="item_name">${item.name}</div>
                            <div class="item_size">${item.size}</div>
                            <div class="item_price">¥${item.price.toLocaleString()}</div>
                            <div class="quantity_controls">
                                <span class="quantity_label">数量</span>
                                <button class="quantity_btn" onclick="decreaseQuantity(${item.id})">-</button>
                                <input type="number" class="quantity-input" value="${item.quantity}" id="qty${item.id}" readonly>
                                <button class="quantity_btn" onclick="increaseQuantity(${item.id})">+</button>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += cartItemHTML;
            });
        }

        // カートタイトルを更新
        function updateCartTitle() {
            const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cart_title').textContent = `カートに入っている商品：${totalItems}点`;
        }

        // 合計金額を更新
        function updateTotal() {
            const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            document.querySelector('.total_price').textContent = `¥${total.toLocaleString()}`;
        }

        // 数量を増やす
        function increaseQuantity(itemId) {
            const item = cartItems.find(item => item.id === itemId);
            if (item) {
                item.quantity++;
                document.getElementById('qty' + itemId).value = item.quantity;
                updateCartTitle();
                updateTotal();
            }
        }

        // 数量を減らす
        function decreaseQuantity(itemId) {
            const item = cartItems.find(item => item.id === itemId);
            if (item && item.quantity > 1) {
                item.quantity--;
                document.getElementById('qty' + itemId).value = item.quantity;
                updateCartTitle();
                updateTotal();
            }
        }