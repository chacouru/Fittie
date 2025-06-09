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
        JOIN brands b ON fb.brand_id = b.id
        WHERE fb.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
try {
    $pdo = new PDO('mysql:host=localhost;dbname=fitty;charset=utf8', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // 商品一覧を取得
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "データベース接続エラー: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品一覧</title>
    <link rel="stylesheet" href="../CSS/products.css">
</head>
<body>
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
    
    <main class="product_list">
    <div id="title">
        <h1>Fashion Store</h1>
    </div>
    <h2>商品一覧</h2>
    <div class="products">
        <?php foreach ($products as $row): ?>
            <div class="product_card">
                <img src="./img/products/ <?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p>¥<?= number_format($row['price']) ?></p>
                <a href="product_detail.php?id=<?= $row['id'] ?>">詳細を見る</a>
            </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>
