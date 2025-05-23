<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>fitty. | トップページ</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/index.css">
</head>

<body>
      <!-- headerここから -->
  <header class="header">
    <button class="menu_button" id="menuToggle" aria-label="メニューを開閉" aria-expanded="false" aria-controls="globalMenu"> <span class="bar"></span><span class="bar"></span><span class="bar"></span> </button>
    <div class="header_logo">
      <h1>fitty.</h1>
    </div>
    <nav class="header_nav"> 
      <a href="./mypage.php" class="icon-user" title="マイページ">👤</a> 
      <a href="./cart.php" class="icon-cart" title="カート">🛒</a> 
      <a href="./search.php" class="icon-search" title="検索">🔍</a> 
      <a href="./contact.php" class="icon-contact" title="お問い合わせ">✉️</a> 
    </nav>
  </header>
  <div class="backdrop" id="menuBackdrop"></div>
  <div class="menu_overlay" id="globalMenu" role="navigation" aria-hidden="true">
    <nav>
      <a href="#" role="menuitem" class="bland brand1">ブランドA</a>
      <a href="#" role="menuitem" class="bland brand2">ブランドB</a>
      <a href="#" role="menuitem" class="bland brand3">ブランドC</a>
      <a href="#" role="menuitem" class="bland brand4">ブランドD</a>
    </nav>
  </div>
  <div class="header_space"></div>
  <!-- headerここまで -->
    <main>
        <div id="scroll_contents">
            <div id="slideshow">
                <img src="../PHP/img/slide1.jpg" class="slide active">
                <img src="../PHP/img/slide2.jpg" class="slide">
                <img src="../PHP/img/slide3.jpg" class="slide">
            </div>
            <h1>最近見たもの</h1>
            <div id="history">
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
            </div>
            <h1>おすすめ商品</h1>
            <div id="recommend">
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
            </div>
            <h1>セール商品</h1>
            <div id="sale">
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
                <div class="product_genre"></div>
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
    <script src="../JavaScript/index.js"></script>
    <script src="../JavaScript/hamburger.js"></script>
</body>

</html>