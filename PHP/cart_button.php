<?php
function displayCartButton($productId, $productName, $stock, $price) {
    echo <<<HTML
    <button class="add-to-cart"
        data-id="{$productId}"
        data-name="{$productName}">
        カートに追加
    </button>
HTML;
}
?>
