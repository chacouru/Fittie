<?php
require_once __DIR__ . '/login_function/functions.php';
$user_id = check_login(); // 未ログインの場合は login.php にリダイレクト
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>fitty. | カート</title>
  <link rel="stylesheet" href="../CSS/reset.css">
  <link rel="stylesheet" href="../CSS/common.css">
  <link rel="stylesheet" href="../CSS/cart.css">
</head>

<body>
  <!-- headerここから -->
  <header class="header">
    <button class="menu_button" id="menuToggle" aria-label="メニューを開閉" aria-expanded="false" aria-controls="globalMenu">
      <span class="bar"></span><span class="bar"></span><span class="bar"></span>
    </button>
    <div class="header_logo">
      <h1><a href="./toppage.php">fitty.</a></h1>
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
    <div class="container">
      <h1 class="cart_title" id="cart_title">カートに入っている商品：読み込み中...</h1>

      <div id="cart_items_container">
        <!-- 商品アイテムがここに動的に表示されます -->
        <div style="text-align: center; padding: 40px;">
          <p>カート情報を読み込み中...</p>
        </div>
      </div>

      <div class="total_section">
        <div class="total_label">合計（税込）</div>
        <div class="total_price">¥0</div>
      </div>

      <button class="checkout_btn" onclick="proceedToCheckout()">レジへ進む</button>
    </div>
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

<script src="../JavaScript/hamburger.js"></script>
<script src="../JavaScript/cart.js"></script>
<script>
// レジへ進む機能
function proceedToCheckout() {
    // カート内の商品数を確認
    const cartTitle = document.getElementById('cart_title').textContent;
    const itemCount = parseInt(cartTitle.match(/\d+/)[0]);
    
    if (itemCount === 0) {
        alert('カートに商品が入っていません');
        return;
    }
    
    // チェックアウトページへ遷移（まだ未実装の場合はアラート）
    if (confirm('注文手続きに進みますか？')) {
        // window.location.href = 'checkout.php';
        alert('チェックアウト機能は現在開発中です');
    }
}
</script>
</body>
</html>