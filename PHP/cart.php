<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>カート | fitty.</title>
  <link rel="stylesheet" href="../CSS/reset.css">
  <link rel="stylesheet" href="../CSS/common.css">
  <link rel="stylesheet" href="../CSS/cart.css">
</head>

<body>
  <!-- headerここから -->
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
    <!-- headerここまで -->
<main>
          <div class="container">
        <h1 class="cart_title" id="cart_title">カートに入っている商品：0点</h1>
        
        <div id="cart_items_container">
            <!-- 商品アイテムがここに動的に表示されます -->
        </div>

        <div class="total_section">
            <div class="total_label">合計（税込）</div>
            <div class="total_price">¥3,456</div>
        </div>

        <button class="checkout_btn">レジへ進む</button>
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


<script src="../JavaScript/hamburger.js"></script>
<script src="../JavaScript/cart.js"></script>
</body>



</html>