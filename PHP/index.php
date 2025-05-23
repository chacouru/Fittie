<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>fitty.|トップページ</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/index.css">
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
  <!-- header -->
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
        <h2>Fitty</h2>
      </div>
      <div class="footer_links">
        <a href="./overview.php">会社概要</a>
        <a href="./terms.php">利用規約</a>
        <a href="./privacy.php">プライバシーポリシー</a>
      </div>
      <div class="footer_sns">
        <a href="#" aria-label="Twitter"><img src="icons/twitter.svg" alt="Twitter"></a>
        <a href="#" aria-label="Instagram"><img src="icons/instagram.svg" alt="Instagram"></a>
        <a href="#" aria-label="Facebook"><img src="icons/facebook.svg" alt="Facebook"></a>
      </div>
      <div class="footer_copy">
        <small>&copy; 2025 Fitty All rights reserved.</small>
      </div>
    </div>
  </footer>
  <!-- footer -->
    <script src="../JavaScript/index.js"></script>
</body>

</html>