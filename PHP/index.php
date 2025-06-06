

<?php
require_once __DIR__ . '/login_function/session.php';
require_once 'cart_button.php'; // カートボタン用関数

// DB接続
$host = 'localhost';
$dbname = 'fitty';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('データベース接続エラー: ' . $e->getMessage());
}

// 閲覧履歴商品
$recent_products = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT p.*, c.name as category_name, b.name as brand_name, vh.viewed_at
        FROM view_history vh
        JOIN products p ON vh.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE vh.user_id = ? AND p.is_active = 1
        ORDER BY vh.viewed_at DESC
        LIMIT 10
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

// 商品カード関数
function displayProductCard($product) {
    // ブランド名をトリムして不要な空白や改行を除去
    $brand_name = isset($product['brand_name']) ? trim($product['brand_name']) : 'no-brand';

    // ブランド名を使って安全なフォルダ名を生成
    $safe_brand_folder = preg_replace('/[^\w\-]/u', '_', $brand_name);

    // 画像ファイル名とフォルダ名でパスを生成
    $image_file = $product['image'] ?? 'no-image.png';
    $image_path = "../PHP/img/products/{$safe_brand_folder}/{$image_file}";

    // セール情報処理
    $display_price = $product['price'];
    $sale_info = '';
    if ($product['is_on_sale'] && $product['sale_price'] && $product['sale_price'] > 0) {
        $display_price = $product['sale_price'];
        $discount_rate = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
        $sale_info = "<span class='original-price'>¥" . number_format($product['price']) . "</span><span class='sale-badge'>{$discount_rate}%OFF</span>";
    }

    // 新着ラベルの判定（7日以内）
    $is_new = false;
    if (isset($product['created_at'])) {
        $created_date = new DateTime($product['created_at']);
        $now = new DateTime();
        $diff = $now->diff($created_date);
        $is_new = $diff->days <= 7;
    }

    // 出力
    echo "<div class='product-card' data-product-id='{$product['id']}'>";
    echo "<div class='product-image' onclick=\"window.location.href='./product_detail.php?id={$product['id']}'\">";
    echo "<img src='{$image_path}' alt='{$product['name']}' onerror=\"this.src='../PHP/img/no-image.png'\">";
    if ($product['is_on_sale']) echo "<div class='sale-label'>SALE</div>";
    if ($is_new) echo "<div class='new-label'>NEW</div>";
    echo "</div>";

    echo "<div class='product-info'>";
    echo "<div class='product-brand'>{$brand_name}</div>";
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
<html lang="ja">
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
            <?php
            if (isset($_SESSION['user_id'])) {
                echo "ログイン中";
            } else {
                echo '<a href="login.php">🚪</a>';
            }
            ?>
            <a href="./mypage.php" class="icon-user" title="マイページ">👤</a>
            <a href="./cart.php" class="icon-cart" title="カート">🛒</a>
            <a href="./search.php" class="icon-search" title="検索">🔍</a>
            <a href="./contact.php" class="icon-contact" title="お問い合わせ">✉️</a>
        </nav>
    </header>
    
    <div class="backdrop" id="menuBackdrop"></div>
    <div class="menu_overlay" id="globalMenu" role="navigation" aria-hidden="true">
        <nav>
            <?php foreach ($brands as $brand): ?>
                <a href="./search.php?brand_id=<?= $brand['id'] ?>" role="menuitem" class="bland">
                    <?= htmlspecialchars($brand['name']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
    <div class="header_space"></div>
    <!-- headerここまで -->

    <main>
        <div id="scroll_contents">
            <div id="slideshow">
                <div class="slide-container">
                    <div class="slide active">
                       <img src="../PHP/img/slideshow/img1.avif" alt="最新コレクション">
                        <div class="slide-content">
                            <h2>2025年春夏コレクション</h2>
                            <p>今季注目のトレンドアイテムが続々登場</p>
                            <a href="./search.php" class="slide-btn">今すぐチェック</a>
                        </div>
                    </div>
                    <div class="slide">
                       <img src="../PHP/img/slideshow/img1.avif" alt="セール情報">
                        <div class="slide-content">
                            <h2>期間限定セール開催中</h2>
                            <p>人気ブランドが最大70%OFF</p>
                            <a href="./search.php?sale=1" class="slide-btn">セール商品を見る</a>
                        </div>
                    </div>
                    <div class="slide">
                        <img src="../PHP/img/slideshow/img1.avif" alt="新着アイテム">
                        <div class="slide-content">
                            <h2>注目の新着アイテム</h2>
                            <p>厳選されたブランドから新作が入荷</p>
                            <a href="./search.php?sort=new" class="slide-btn">新着を見る</a>
                        </div>
                    </div>
                </div>
                
                <!-- スライド操作ボタン -->
                <button class="slide-nav prev" onclick="prevSlide()" aria-label="前のスライド">&#8249;</button>
                <button class="slide-nav next" onclick="nextSlide()" aria-label="次のスライド">&#8250;</button>
                
                <!-- スライドドット -->
                <div class="slide-dots">
                    <button class="dot active" onclick="currentSlide(1)"></button>
                    <button class="dot" onclick="currentSlide(2)"></button>
                    <button class="dot" onclick="currentSlide(3)"></button>
                </div>
            </div>
<?php
// index.phpの商品セクション部分を以下のように変更

// 最近見たもの
if (isset($_SESSION['user_id']) && !empty($recent_products)): ?>
    <div class="product-section">
        <h1 class="section-title">最近見たもの</h1>
        <div class="carousel-container">
            <button class="carousel-nav prev" onclick="slideCarousel('history', -1)">❮</button>
            <div class="carousel-wrapper">
                <div id="history" class="carousel-track">
                    <?php foreach ($recent_products as $product): ?>
                        <?php displayProductCard($product); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="carousel-nav next" onclick="slideCarousel('history', 1)">❯</button>
        </div>
    </div>
<?php elseif (isset($_SESSION['user_id'])): ?>
    <div class="product-section">
        <h1 class="section-title">最近見たもの</h1>
        <div class="no-products">
            まだ商品を閲覧していません。商品を見て回ってみましょう！
        </div>
    </div>
<?php endif; ?>

<!-- おすすめ商品 -->
<div class="product-section">
    <h1 class="section-title">おすすめ商品</h1>
    <?php if (!empty($recommended_products)): ?>
        <div class="carousel-container">
            <button class="carousel-nav prev" onclick="slideCarousel('recommend', -1)">❮</button>
            <div class="carousel-wrapper">
                <div id="recommend" class="carousel-track">
                    <?php foreach ($recommended_products as $product): ?>
                        <?php displayProductCard($product); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="carousel-nav next" onclick="slideCarousel('recommend', 1)">❯</button>
        </div>
    <?php else: ?>
        <div class="no-products">現在おすすめ商品はありません</div>
    <?php endif; ?>
</div>

<!-- 新着アイテム -->
<?php if (!empty($new_products)): ?>
    <div class="product-section">
        <h1 class="section-title">新着アイテム</h1>
        <div class="carousel-container">
            <button class="carousel-nav prev" onclick="slideCarousel('new-arrivals', -1)">❮</button>
            <div class="carousel-wrapper">
                <div id="new-arrivals" class="carousel-track">
                    <?php foreach ($new_products as $product): ?>
                        <?php displayProductCard($product); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="carousel-nav next" onclick="slideCarousel('new-arrivals', 1)">❯</button>
        </div>
    </div>
<?php endif; ?>

<!-- セール商品 -->
<?php if (!empty($sale_products)): ?>
    <div class="product-section">
        <h1 class="section-title">セール商品</h1>
        <div class="carousel-container">
            <button class="carousel-nav prev" onclick="slideCarousel('sale', -1)">❮</button>
            <div class="carousel-wrapper">
                <div id="sale" class="carousel-track">
                    <?php foreach ($sale_products as $product): ?>
                        <?php displayProductCard($product); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="carousel-nav next" onclick="slideCarousel('sale', 1)">❯</button>
        </div>
    </div>
<?php endif; ?>


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
    <script src="../JavaScript/slideshow.js"></script>
    <script src="../JavaScript/carousel.js"></script>

</body>
</html>