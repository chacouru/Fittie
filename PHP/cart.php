<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="../CSS/common.css">
  <link rel="stylesheet" href="../CSS/reset.css">
  <link rel="stylesheet" href="../CSS/cart.css">
</head>

<body>
   <header class="header">
    <div class="header_container">
      <div class="header_menu">
        <div class="menu_button" id="menuToggle">
          <span class="bar"></span>
          <span class="bar"></span>
          <span class="bar"></span>
        </div>
        <div class="menu_overlay">
          <a href="#" class="brand_link link1">ブランドA</a>
          <a href="#" class="brand_link link2">ブランドB</a>
          <a href="#" class="brand_link link3">ブランドC</a>
          <a href="#" class="brand_link link4">ブランドD</a>
        </div>
      </div>
      <div class="header_logo">
        <h1>Fitty</h1>
      </div>
      <nav class="header_nav">
        <a href="#">マイページ</a>
        <a href="#">カート</a>
        <a href="#">検索</a>
        <a href="#">お問い合わせ</a>
      </nav>
    </div>
  </header>
  <div class="header_space"></div>
<main>
  
      <div class="container">
          <h1 class="cart-title">カートに入っている商品：○○点</h1>
  
          <div class="cart-item">
              <div class="item-image"></div>
              <div class="item-details">
                  <div class="item-brand">ブランド名</div>
                  <div class="item-name">商品名</div>
                  <div class="item-size">サイズ</div>
                  <div class="item-price">価格</div>
                  <div class="quantity-controls">
                      <span class="quantity-label">数量</span>
                      <button class="quantity-btn" onclick="decreaseQuantity(1)">-</button>
                      <input type="number" class="quantity-input" value="1" id="qty1" readonly>
                      <button class="quantity-btn" onclick="increaseQuantity(1)">+</button>
                  </div>
              </div>
          </div>
  
          <div class="cart-item">
              <div class="item-image"></div>
              <div class="item-details">
                  <div class="item-brand">ブランド名</div>
                  <div class="item-name">商品名</div>
                  <div class="item-size">サイズ</div>
                  <div class="item-price">価格</div>
                  <div class="quantity-controls">
                      <span class="quantity-label">数量</span>
                      <button class="quantity-btn" onclick="decreaseQuantity(2)">-</button>
                      <input type="number" class="quantity-input" value="1" id="qty2" readonly>
                      <button class="quantity-btn" onclick="increaseQuantity(2)">+</button>
                  </div>
              </div>
          </div>
</main>


      <!-- footer -->
  <footer class="footer">
    <div class="footer_container">
      <div class="footer_logo">
        <h2>fitty.</h2>
      </div>
      <div class="footer_links">
        <a href="#">会社概要</a>
        <a href="#">利用規約</a>
        <a href="#">プライバシーポリシー</a>
      </div>
      <div class="footer_sns">
        <a href="#" aria-label="Twitter"><img src="icons/twitter.svg" alt="Twitter"></a>
        <a href="#" aria-label="Instagram"><img src="icons/instagram.svg" alt="Instagram"></a>
        <a href="#" aria-label="Facebook"><img src="icons/facebook.svg" alt="Facebook"></a>
      </div>
      <div class="footer_copy">
        <small>&copy; 2025 fitty. All rights reserved.</small>
      </div>
    </div>
  </footer>
  <!-- footer -->



</body>

<script src="../JavaScript/hamburger.js"></script>

    <script>
        function increaseQuantity(itemId) {
            const input = document.getElementById('qty' + itemId);
            let currentValue = parseInt(input.value);
            input.value = currentValue + 1;
        }

        function decreaseQuantity(itemId) {
            const input = document.getElementById('qty' + itemId);
            let currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        }
    </script>

</html>