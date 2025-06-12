<?php
// エラー表示を有効にする（開発時のみ）
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/login_function/session.php';
    
    // $pdoの存在確認
    if (!isset($pdo)) {
        throw new Exception('データベース接続が確立されていません。');
    }
    
    // user_idの初期化
    $user_id = $_SESSION['user_id'] ?? null;
    $brands = [];
    
    // お気に入りブランド取得（ハンバーガーメニュー表示用）
    if ($user_id) {
        $stmt = $pdo->prepare("
            SELECT b.id, b.name 
            FROM favorite_brands fb
            JOIN brands b ON fb.brand_id = b.id
            WHERE fb.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $brands = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // ユーザー情報取得
    $user = null;
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // ログアウト処理またはエラーハンドリング
            session_destroy();
            header('Location: ./login.php');
            exit;
        }
    }

    // 閲覧履歴商品（修正版）
    $recent_products = [];
    if ($user_id) {
        try {
            $stmt = $pdo->prepare("
                SELECT DISTINCT p.*, c.name as category_name, b.name as brand_name,
                       vh.viewed_at
                FROM view_history vh
                JOIN products p ON vh.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE vh.user_id = ? AND p.is_active = 1 AND p.stock > 0
                ORDER BY vh.viewed_at DESC
                LIMIT 10
            ");
            $stmt->execute([$user_id]);
            $recent_products = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("閲覧履歴取得エラー: " . $e->getMessage());
            $recent_products = [];
        }
    }

    // 人気商品
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, b.name as brand_name,
                   COALESCE(COUNT(oi.id), 0) as order_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
            WHERE p.is_active = 1 AND p.stock > 0
            GROUP BY p.id, p.name, p.price, p.sale_price, p.is_on_sale, p.image, p.created_at, 
                     p.rating, p.review_count, p.category_id, p.brand_id, p.stock, p.is_active,
                     c.name, b.name
            ORDER BY order_count DESC, p.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $popular_products = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("人気商品取得エラー: " . $e->getMessage());
        $popular_products = [];
    }

    // おすすめ
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, b.name as brand_name,
                   COALESCE(COUNT(vh.id), 0) as view_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN view_history vh ON p.id = vh.product_id
            WHERE p.is_active = 1 AND p.stock > 0
            GROUP BY p.id, p.name, p.price, p.sale_price, p.is_on_sale, p.image, p.created_at,
                     p.rating, p.review_count, p.category_id, p.brand_id, p.stock, p.is_active,
                     c.name, b.name
            ORDER BY view_count DESC, p.rating DESC, p.review_count DESC, p.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $recommended_products = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("おすすめ商品取得エラー: " . $e->getMessage());
        $recommended_products = [];
    }

    // セール
    try {
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
        $sale_products = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("セール商品取得エラー: " . $e->getMessage());
        $sale_products = [];
    }

    // 新着
    try {
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
        $new_products = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("新着商品取得エラー: " . $e->getMessage());
        $new_products = [];
    }

} catch (Exception $e) {
    // 致命的なエラーの場合
    error_log("Index.php 致命的エラー: " . $e->getMessage());
    die("システムエラーが発生しました。管理者にお問い合わせください。");
}

// 2. 商品取得関数（JOINでブランド情報も取得）
function getProductsWithBrands($pdo, $limit = null, $category_id = null)
{
    try {
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("getProductsWithBrands エラー: " . $e->getMessage());
        return [];
    }
}

// 3. 修正されたdisplayProductCard関数
function displayProductCard($product)
{
    if (!$product || !is_array($product)) {
        return;
    }

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
    $display_price = $product['price'] ?? 0;
    $sale_info = '';
    if (($product['is_on_sale'] ?? false) && ($product['sale_price'] ?? 0) > 0) {
        $display_price = $product['sale_price'];
        $original_price = $product['price'] ?? 0;
        if ($original_price > 0) {
            $discount_rate = round((($original_price - $product['sale_price']) / $original_price) * 100);
            $sale_info = "<span class='original-price'>¥" . number_format($original_price) . "</span>
                          <span class='sale-badge'>{$discount_rate}%OFF</span>";
        }
    }

    // 新着判定（7日以内）
    $is_new = false;
    if (!empty($product['created_at'])) {
        try {
            $created_date = new DateTime($product['created_at']);
            $is_new = (new DateTime())->diff($created_date)->days <= 7;
        } catch (Exception $e) {
            $is_new = false;
        }
    }

    // 商品カード出力
    $product_id = $product['id'] ?? 0;
    $product_name = htmlspecialchars($product['name'] ?? '商品名なし', ENT_QUOTES, 'UTF-8');
    $brand_name_safe = htmlspecialchars($brand_name, ENT_QUOTES, 'UTF-8');

    echo "<div class='product-card' data-product-id='{$product_id}'>";
    echo "<div class='product-image' onclick=\"window.location.href='./product_detail.php?id={$product_id}'\">";
    echo "<img src='{$image_path}' alt='{$product_name}'>";

    if ($product['is_on_sale'] ?? false) echo "<div class='sale-label'>SALE</div>";
    if ($is_new) echo "<div class='new-label'>NEW</div>";
    echo "</div>";

    echo "<div class='product-info'>";
    echo "<div class='product-brand'>{$brand_name_safe}</div>";
    echo "<div class='product-name'>{$product_name}</div>";
    echo "<div class='product-price'><span class='current-price'>¥" . number_format($display_price) . "</span>{$sale_info}</div>";
    echo "</div></div>";
}

// カルーセル表示関数
function displayProductCarousel($products, $section_id, $section_title)
{
    if (empty($products)) {
        echo "<div class='no-products'>現在{$section_title}はありません</div>";
        return;
    }

    echo "<h1 class='section-title'>" . htmlspecialchars($section_title, ENT_QUOTES, 'UTF-8') . "</h1>";
    echo "<div class='carousel-container' id='" . htmlspecialchars($section_id, ENT_QUOTES, 'UTF-8') . "'>";
    echo "<div class='carousel-wrapper'>";
    echo "<div class='carousel-track'>";

    foreach ($products as $product) {
        displayProductCard($product);
    }

    echo "</div></div>";

    // ナビゲーションボタン
    $section_id_safe = htmlspecialchars($section_id, ENT_QUOTES, 'UTF-8');
    echo "<button class='carousel-nav prev' onclick='moveCarousel(\"{$section_id_safe}\", -1)' aria-label='前の商品'>‹</button>";
    echo "<button class='carousel-nav next' onclick='moveCarousel(\"{$section_id_safe}\", 1)' aria-label='次の商品'>›</button>";

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
  <a href="logout.php">
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

            <!-- 最近見たもの -->
            <?php if ($user_id && !empty($recent_products)): ?>
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
            <?php elseif ($user_id): ?>
                <div class="product-section">
                    <h1 class="section-title">最近見たもの</h1>
                    <div class="no-products">
                        まだ商品を閲覧していません。商品を見て回ってみましょう！
                    </div>
                </div>
            <?php endif; ?>

            <!-- 人気商品 -->
            <?php if (!empty($popular_products)): ?>
                <div class="product-section">
                    <h1 class="section-title">人気商品</h1>
                    <div class="carousel-container">
                        <button class="carousel-nav prev" onclick="slideCarousel('popular', -1)">❮</button>
                        <div class="carousel-wrapper">
                            <div id="popular" class="carousel-track">
                                <?php foreach ($popular_products as $product): ?>
                                    <?php displayProductCard($product); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button class="carousel-nav next" onclick="slideCarousel('popular', 1)">❯</button>
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

    <!-- フッターここから -->
 <footer class="footer">
    <div class="footer_container">
        <a href="index.php"><div class="footer_logo"><h2>fitty.</h2></div></a>
        <div class="footer_links">
            <a href="./overview.php">会社概要</a>
            <a href="./terms.php">利用規約</a>
            <a href="./privacy.php">プライバシーポリシー</a>
            <a href="./qa.php">よくある質問</a>
        </div>
        <div class="footer_sns">
            <a href="#"><img src="img/sns_icon/twitter.png" alt="Twitter"><a>
            <a href="#"><img src="img/sns_icon/instagram.png" alt="Instagram"><a>
            <a href="#"><img src="img/sns_icon/youtube.png" alt="YouTube"><a>
        </div>
        <div class="footer_copy">
            <small>&copy; 2025 Fitty All rights reserved.</small>
        </div>
    </div>
</footer>
<!-- フッターここまで -->

    <script src="../JavaScript/hamburger.js"></script>
    <script src="../JavaScript/slideshow.js"></script>
    <script src="../JavaScript/carousel.js"></script>
    <script src="../JavaScript/cart_button.js"></script>

</body>

</html>