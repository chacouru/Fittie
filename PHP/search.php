<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>fitty. | 探す</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/search.css">
</head>

<body>
   <!-- headerここから -->
  <header class="header">
    <button class="menu_button" id="menuToggle" aria-label="メニューを開閉" aria-expanded="false" aria-controls="globalMenu"> <span class="bar"></span><span class="bar"></span><span class="bar"></span> </button>
    <div class="header_logo">
      <h1><a href="./index.php">fitty.</a></h1>
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
        <form action="" class="form_box">
            <h1>探す</h1>
        <div class="form_container">
        <div class="select_container">
            <select name="brand">
                <option value="" selected disabled hidden>ブランド</option>
                <option value="1">Woven Whisper</option>
                <option value="2">Lush Loom</option>
                <option value="3">Velvet Verse</option>
                <option value="4">Urban Threads</option>
                <option value="5">Chic Beacon</option>
                <option value="6">Fad Fizz</option>
                <option value="7">ADOOR</option>
                <option value="8">KARAQURI</option>
                <option value="9">FAR-EAST</option>
                <option value="10">ON°</option>
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

<footer class="footer">
    <div class="footer_container">
      <a href="index.php">
        <div class="footer_logo">
          <h2>fitty.</h2>
        </div>
      </a>
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
</body>
<script src="../JavaScript/hamburger.js"></script>
</html>