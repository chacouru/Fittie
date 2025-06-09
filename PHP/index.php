<?php
require_once __DIR__ . '/login_function/session.php';
require_once 'db_connect.php'; // DB接続ファイルを読み込む

$brands = [];
$user_id = null; // ← エラー防止のため初期化

// ログインしている場合、お気に入りブランドを取得
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT b.id, b.name 
        FROM favorite_brands fb
        JOIN brands b ON fb.brand_id = b.id
        WHERE fb.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// おすすめ
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, b.name as brand_name,
           COUNT(vh.id) as view_count
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN view_history vh ON p.id = vh.product_id
    WHERE p.is_active = 1 AND p.stock > 0
    GROUP BY p.id
    ORDER BY view_count DESC, p.rating DESC, p.review_count DESC, p.created_at DESC
    LIMIT 10
");
$stmt->execute();
$recommended_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// セール
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, b.name as brand_name,
           CASE 
               WHEN p.sale_price IS NOT NULL AND p.sale_price > 0 
               THEN ((p.price - p.sale_price) / p.price) * 100 
               ELSE 0 
           END as discount_rate
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    WHERE p.is_active = 1 AND p.is_on_sale = 1 AND p.stock > 0
    ORDER BY discount_rate DESC, p.created_at DESC
    LIMIT 10
");
$stmt->execute();
$sale_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 新着
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, b.name as brand_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    WHERE p.is_active = 1 AND p.stock > 0
    ORDER BY p.created_at DESC
    LIMIT 10
");
$stmt->execute();
$new_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. 商品取得関数（JOINでブランド情報も取得）
function getProductsWithBrands($pdo, $limit = null, $category_id = null) {
    $sql = "SELECT 
                p.*, 
                b.name as brand_name,
                b.folder_name as brand_folder
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.is_active = 1";
    
    if ($category_id) {
        $sql .= " AND p.category_id = :category_id";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT :limit";
    }
    
    $stmt = $pdo->prepare($sql);
    
    if ($category_id) {
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    }
    if ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 3. 修正されたdisplayProductCard関数
function displayProductCard($product) {
    // データベースから取得したブランド情報を使用
    $brand_name = 'default';
    $safe_brand_folder = 'default';
    
    if (isset($product['brand_name']) && trim($product['brand_name']) !== '') {
        $brand_name = trim($product['brand_name']);
        // brand_folderがある場合はそれを使用、なければbrand_nameを使用
        $safe_brand_folder = isset($product['brand_folder']) && trim($product['brand_folder']) !== '' 
            ? trim($product['brand_folder']) 
            : $brand_name;
    }
    
    // 画像パス候補の構築
    $image_file = $product['image'] ?? 'no-image.png';
    $possible_paths = [
        "../PHP/img/products/{$safe_brand_folder}/{$image_file}",
        "../PHP/img/products/default/{$image_file}",
        "../PHP/img/products/{$image_file}",
        "../PHP/img/no-image.png"
    ];
    
    $image_path = "../PHP/img/no-image.png";
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $image_path = $path;
            break;
        }
    }
    
    // 価格・セール処理
    $display_price = $product['price'];
    $sale_info = '';
    if ($product['is_on_sale'] && $product['sale_price'] > 0) {
        $display_price = $product['sale_price'];
        $discount_rate = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
        $sale_info = "<span class='original-price'>¥" . number_format($product['price']) . "</span>
                      <span class='sale-badge'>{$discount_rate}%OFF</span>";
    }
    
    // 新着判定（7日以内）
    $is_new = false;
    if (!empty($product['created_at'])) {
        $created_date = new DateTime($product['created_at']);
        $is_new = (new DateTime())->diff($created_date)->days <= 7;
    }
    
    // 商品カード出力
    echo "<div class='product-card' data-product-id='{$product['id']}'>";
    echo "<div class='product-image' onclick=\"window.location.href='./product_detail.php?id={$product['id']}'\">";
    echo "<img src='{$image_path}' alt='" . htmlspecialchars($product['name']) . "'>";
    
    if ($product['is_on_sale']) echo "<div class='sale-label'>SALE</div>";
    if ($is_new) echo "<div class='new-label'>NEW</div>";
    echo "</div>";
    
    echo "<div class='product-info'>";
    echo "<div class='product-brand'>" . htmlspecialchars($brand_name) . "</div>";
    echo "<div class='product-price'><span class='current-price'>¥" . number_format($display_price) . "</span>{$sale_info}</div>";
    displayCartButton($product['id'], $product['name'], $product['stock'], $product['price']);
    echo "</div></div>";
}



// カルーセル表示関数
function displayProductCarousel($products, $section_id, $section_title) {
    if (empty($products)) {
        echo "<div class='no-products'>現在{$section_title}はありません</div>";
        return;
    }
    
    echo "<h1 class='section-title'>{$section_title}</h1>";
    echo "<div class='carousel-container' id='{$section_id}'>";
    echo "<div class='carousel-wrapper'>";
    echo "<div class='carousel-track'>";
    
    foreach ($products as $product) {
        displayProductCard($product);
    }
    
    echo "</div></div>";
    
    // ナビゲーションボタン
    echo "<button class='carousel-nav prev' onclick='moveCarousel(\"{$section_id}\", -1)' aria-label='前の商品'>‹</button>";
    echo "<button class='carousel-nav next' onclick='moveCarousel(\"{$section_id}\", 1)' aria-label='次の商品'>›</button>";
    
    echo "</div>";
}
?>
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
<!-- headerここから -->
<header class="header">
  <button class="menu_button" id="menuToggle" aria-label="メニューを開閉" aria-expanded="false" aria-controls="globalMenu">
    <span class="bar"></span><span class="bar"></span><span class="bar"></span>
  </button>
  <div class="header_logo">
    <h1><a href="./index.php">fitty.</a></h1>
  </div>
  <nav class="header_nav"> 
    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="login_logout_img">
        <a href="logout.php">
          <img src="./img/logout.jpg" alt="ログアウト">
        </a>
      </div>
    <?php else: ?>
      <div class="login_logout_img">
        <a href="login.php">
          <img src="./img/login.png" alt="ログイン">
        </a>
      </div>
    <?php endif; ?>
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
      <?php for ($i = 0; $i < 11; $i++): ?>
        <div class="product_genre"></div>
      <?php endfor; ?>
    </div>
    <h1>おすすめ商品</h1>
    <div id="recommend">
      <?php for ($i = 0; $i < 10; $i++): ?>
        <div class="product_genre"></div>
      <?php endfor; ?>
    </div>
    <h1>セール商品</h1>
    <div id="sale">
      <?php for ($i = 0; $i < 10; $i++): ?>
        <div class="product_genre"></div>
      <?php endfor; ?>
    </div>
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
</body>
</html>
