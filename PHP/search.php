<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>探す | fitty.</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/search.css">
</head>

<body>
    <!-- header -->
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
        <h1>fitty.</h1>
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
        <form action="" class="form_box">
            <h1>探す</h1>
        <div class="form_container">
        <div class="select_container">
            <select name="brand">
                <option value="" selected disabled hidden>ブランド</option>
                <option value="1">HARE</option>
                <option value="2">ONCILY</option>
                <option value="3">LACOSTE</option>
            </select>
        </div>
        <div class="select_container">
            <select name="color">
                <option value="" selected disabled hidden>カラー</option>
                <option value="1">ブラック</option>
                <option value="2">ホワイト</option>
                <option value="3">レッド</option>
            </select>
        </div>
        <div class="select_container">
            <select name="genre">
                <option value="" selected disabled hidden>ジャンル</option>
                <option value="1">トップス</option>
                <option value="2">パンツ</option>
                <option value="3">シューズ</option>
            </select>
        </div>
        </div>
         <div class="button_container">
             <button type="submit">この条件で探す</button>
             <input type="reset" value="リセット">
          </div>
          </form>
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
</html>