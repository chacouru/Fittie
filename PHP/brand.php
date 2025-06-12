<?php
session_start();
require_once 'db_connect.php';
require_once 'cart_button.php';

$brand_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($brand_id <= 0) {
    echo "ブランドIDが不正です。";
    exit();
}

// ブランド名取得
$stmt = $pdo->prepare("SELECT name FROM brands WHERE id = :id");
$stmt->execute([':id' => $brand_id]);
$brand = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$brand) {
    echo "ブランドが見つかりません。";
    exit();
}

// ブランドの商品一覧取得
$stmt = $pdo->prepare("
    SELECT p.*, b.name AS brand_name, c.name AS category_name 
    FROM products p
    JOIN brands b ON p.brand_id = b.id
    JOIN categories c ON p.category_id = c.id
    WHERE p.brand_id = :brand_id
");
$stmt->execute([':brand_id' => $brand_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// お気に入りブランド取得（ハンバーガーメニュー用）
$favorite_brands = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT b.id, b.name 
        FROM favorite_brands fb
        JOIN brands b ON fb.brand_id = b.id
        WHERE fb.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $favorite_brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($brand['name']) ?> の商品一覧 | fitty.</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/search.css">
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
            <nav class="header_nav"> <?php
    if (isset($_SESSION['user_id'])) {
        echo '<div class="login_logout_img">
  <a href="logout.php">
    <img src="./img/logout.jpg" alt="ログアウト">
  </a>
</div>
';
    } else {
        echo '<div class="login_logout_img">
  <a href="login.php">
    <img src="./img/login.png" alt="ログイン">
  </a>
</div>
';
    }?>
        <a href="./mypage.php" class="icon-user" title="マイページ">👤</a> 
        <a href="./cart.php" class="icon-cart" title="カート">🛒</a> 
        <a href="./search.php" class="icon-search" title="検索">🔍</a> 
        <a href="./contact.php" class="icon-contact" title="お問い合わせ">✉️</a> 
    </nav>
</header>

<div class="backdrop" id="menuBackdrop"></div>

<?php if ($user_id): ?>
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
            <p style="padding: 10px; margin-top:65px;">お気に入りのブランドが登録されていません。</p>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>

<div class="backdrop" id="menuBackdrop"></div>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="menu_overlay" id="globalMenu" role="navigation" aria-hidden="true">
  <nav>
    <?php if (!empty($brands)): ?>
      <?php foreach ($brands as $index => $brand): ?>
        <a href="brand.php?id=<?= htmlspecialchars($brand['id']) ?>"
   role="menuitem"
   class="brand">
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
    <h1 id="title"><?= htmlspecialchars($brand['name']) ?></h1>

    <section class="results">
        <h2>商品一覧</h2>
        <?php if (empty($products)): ?>
            <p>このブランドの商品はありません。</p>
        <?php else: ?>
            <ul>
                <?php foreach ($products as $product): ?>
                    <?php
                    $brand_folder = $product['brand_name'];
                    $image_file = $product['image'];
                    $image_path = "./img/products/" . rawurlencode($brand_folder) . "/" . rawurlencode($image_file);
                    $stock = isset($product['stock']) ? (int)$product['stock'] : 10;
                    ?>
                    <li>
                        <a href="product_detail.php?id=<?= $product['id'] ?>">
                            <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($product['name']) ?>" width="100">
                            <p>商品名：<?= htmlspecialchars($product['name']) ?></p>
                        </a>
                        <p>ジャンル：<?= htmlspecialchars($product['category_name']) ?></p>
                        <p>価格：<?= htmlspecialchars($product['price']) ?>円</p>
                        <?php displayCartButton($product['id'], $product['name'], $stock, $product['price']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>

<!-- フッターここから -->
 <footer class="footer">
    <div class="footer_container">
        <a href="index.php"><div class="footer_logo"><h2>fitty.</h2></div></a>
        <div class="footer_links">
            <a href="./overview.php">会社概要</a>
            <a href="./terms.php">利用規約</a>
            <a href="./privacy.php">プライバシーポリシー</a>
        </div>
        <div class="footer_sns">
            <a href="#"><img src="/PHP/img/sns_icon/twitter.png" alt="Twitter"></a>
            <a href="#"><img src="/PHP/img/sns_icon/instagram.png" alt="Instagram"></a>
            <a href="#"><img src="/PHP/img/sns_icon/youtube.png" alt="YouTube"></a>
        </div>
        <div class="footer_copy">
            <small>&copy; 2025 Fitty All rights reserved.</small>
        </div>
    </div>
</footer>
<!-- フッターここまで -->

<script src="../JavaScript/hamburger.js"></script>
</body>
</html>
