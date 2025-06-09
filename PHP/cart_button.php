<?php
function displayCartButton($id, $name, $stock, $price) {
    // HTML出力
    echo '
    <div class="cart-button-container" data-id="' . htmlspecialchars($id) . '"
         data-name="' . htmlspecialchars($name) . '"
         data-stock="' . htmlspecialchars($stock) . '"
         data-price="' . htmlspecialchars($price) . '">
        <button class="add-to-cart-btn">カートに追加</button>
        <div class="cart-message" style="margin-top: 5px;"></div>
    </div>
    ';
    
    // JS出力（1回だけ）
    static $scriptOutput = false;
    if (!$scriptOutput) {
        echo '
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".add-to-cart-btn").forEach(function (btn) {
                btn.addEventListener("click", function () {
                    const container = this.closest(".cart-button-container");
                    const formData = new FormData();
                    formData.append("id", container.dataset.id);
                    formData.append("name", container.dataset.name);
                    formData.append("stock", container.dataset.stock);
                    formData.append("price", container.dataset.price);

                    fetch("api/add_to_cart.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        const msg = container.querySelector(".cart-message");
                        if (data.success) {
                            msg.textContent = "カートに追加しました";
                            msg.style.color = "green";
                        } else {
                            msg.textContent = "エラーが発生しました";
                            msg.style.color = "red";
                        }
                    })
                    .catch(err => {
                        console.error("通信エラー:", err);
                    });
                });
            });
        });
        </script>
        ';
        $scriptOutput = true;
    }
}
