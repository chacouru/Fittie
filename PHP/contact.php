<?php
session_start();
require_once 'db_connect.php'; // DB接続ファイルを読み込む

$brands = [];

// ログインしている場合、お気に入りブランドを取得
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT b.id, b.name 
        FROM favorite_brands fb
        JOIN brand b ON fb.brand_id = b.id
        WHERE fb.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
}?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>fitty. | お問い合わせ</title>
      <link rel="stylesheet" href="../CSS/common.css">
  <link rel="stylesheet" href="../CSS/reset.css">
  <link rel="stylesheet" href="../CSS/contact.css">
</head>
<body>
  <!-- headerここから -->
<header class="header">
  <button class="menu_button" id="menuToggle" aria-label="メニューを開閉" aria-expanded="false" aria-controls="globalMenu">
    <span class="bar"></span><span class="bar"></span><span class="bar"></span>
  </button>
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

<?php if (isset($_SESSION['user_id'])): ?>
<div class="menu_overlay" id="globalMenu" role="navigation" aria-hidden="true">
  <nav>
    <?php if (!empty($brands)): ?>
      <?php foreach ($brands as $index => $brand): ?>
        <a href="brand.php?id=<?= htmlspecialchars($brand['id']) ?>"
           role="menuitem"
           class="bland"
           style="--index: <?= $index ?>; top: <?= 75 + $index * 50 ?>px; left: <?= 170 - $index * 60 ?>px;">
          <?= htmlspecialchars($brand['name']) ?>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="padding: 10px;">お気に入りのブランドが登録されていません。</p>
    <?php endif; ?>
  </nav>
</div>
<?php endif; ?>

<div class="header_space"></div>
<!-- headerここまで -->
    <main>
        <h1>お問い合わせ</h1>
    
      <div class="contact_container">
        <form action="/send-inquiry" method="POST">
          <label for="name">お名前 <span class="required">*</span></label>
          <input type="text" id="name" name="name" required>
    
          <label for="email">メールアドレス <span class="required">*</span></label>
          <input type="email" id="email" name="email" required>
    
          <label for="subject">件名 <span class="required">*</span></label>
          <input type="text" id="subject" name="subject" required>
    
          <label for="message">お問い合わせ内容 <span class="required">*</span></label>
          <textarea id="message" name="message" required></textarea>
    
          <div class="form_note">※ すべての項目をご記入の上、送信してください。</div>
    
          <button type="submit">送信</button>
        </form>
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
  <!-- footer -->
<script src="../JavaScript/hamburger.js"></script>
</body>
</html>