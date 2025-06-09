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
}
?>
<link rel="stylesheet" href="../CSS/common.css">
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
<script src="../JavaScript/hamburger.js"></script>
