<?php
session_start();
require_once './DbManager.php';

// 商品データを取得する関数（現在のDB構造に合わせて修正）
function getRecentlyViewed($userId, $limit = 11)
{
    try {
        $db = getDb();
        // view_historyテーブルが存在しないため、代替案として最近の注文履歴から取得
        // または、この機能を無効化
        return [];
    } catch (PDOException $e) {
        error_log("最近見た商品の取得エラー: " . $e->getMessage());
        return [];
    }
}

function getRecommendedProducts($userId = null, $limit = 10)
{
    try {
        $db = getDb();
        if ($userId) {
            // ユーザーの購入履歴に基づくおすすめ（簡易版）
            $sql = "SELECT DISTINCT p.id, p.name, p.price, p.image, p.category_id, p.stock, 
                           c.name as category_name, b.name as brand_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN brands b ON p.brand_id = b.id
                    WHERE p.stock > 0
                    AND p.category_id IN (
                        SELECT DISTINCT p2.category_id 
                        FROM products p2 
                        JOIN order_items oi ON p2.id = oi.product_id 
                        JOIN orders o ON oi.order_id = o.id
                        WHERE o.user_id = :user_id
                    )
                    ORDER BY p.created_at DESC
                    LIMIT :limit";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        } else {
            // ゲストユーザー向けの商品（在庫があるもの）
            $sql = "SELECT p.id, p.name, p.price, p.image, p.category_id, p.stock, 
                           c.name as category_name, b.name as brand_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN brands b ON p.brand_id = b.id
                    WHERE p.stock > 0
                    ORDER BY p.created_at DESC
                    LIMIT :limit";
            $stmt = $db->prepare($sql);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("おすすめ商品の取得エラー: " . $e->getMessage());
        return [];
    }
}

function getSaleProducts($limit = 10)
{
    try {
        $db = getDb();
        // 現在のDB構造にはセール関連のカラムがないため、
        // 代替案として価格の安い順で商品を取得
        $sql = "SELECT p.id, p.name, p.price, p.image, p.category_id, p.stock, 
                       c.name as category_name, b.name as brand_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.stock > 0
                ORDER BY p.price ASC
                LIMIT :limit";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("セール商品の取得エラー: " . $e->getMessage());
        return [];
    }
}

function getLowestPriceProducts($limit = 5)
{
    try {
        $db = getDb();
        $sql = "SELECT p.*, c.name AS category_name, b.name as brand_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.stock > 0 AND p.is_active = 1
                ORDER BY p.price ASC
                LIMIT :limit";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("価格が安い商品取得エラー: " . $e->getMessage());
        return [];
    }
}
$cheapProducts = getLowestPriceProducts();

// ブランド情報を取得する関数（brandテーブルが存在するが空のため）
function getBrands()
{
    try {
        $db = getDb();
        $sql = "SELECT id, name FROM brands ORDER BY name";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("ブランド取得エラー: " . $e->getMessage());
        return [];
    }
}

// ユーザーIDを取得（ログインしている場合）
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// データを取得
$recentlyViewed = $userId ? getRecentlyViewed($userId) : [];
$recommendedProducts = getRecommendedProducts($userId);
$saleProducts = getSaleProducts();
$brands = getBrands();

// 価格をフォーマットする関数
function formatPrice($price)
{
    return number_format($price) . '円';
}

// 画像パスを処理する関数（ブランド名対応版）
function getImagePath($imagePath, $brandName = '')
{
    if (empty($imagePath)) {
        return '../PHP/img/no-image.jpg';
    }

    // 絶対パスやURLの場合はそのまま返す
    if (str_starts_with($imagePath, 'http') || str_starts_with($imagePath, '/') || str_starts_with($imagePath, '../')) {
        return $imagePath;
    }

    // ブランド名が指定されている場合はブランドディレクトリを含める
    if (!empty($brandName)) {
        // ブランド名をURLセーフな形式に変換（必要に応じて）
        $safeBrandName = str_replace([' ', '&', '/', '\\'], ['_', '_', '_', '_'], $brandName);
        return '../PHP/img/products/' . $safeBrandName . '/' . $imagePath;
    }

    // ブランド名がない場合は従来通り
    return '../PHP/img/products/' . $imagePath;
}

// 割引率を計算する関数
function calculateDiscountRate($originalPrice, $salePrice)
{
    if ($originalPrice <= 0) return 0;
    return round((($originalPrice - $salePrice) / $originalPrice) * 100);
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
            <a href="./mypage.php" class="icon-user" title="マイページ">👤</a>
            <a href="./cart.php" class="icon-cart" title="カート">🛒</a>
            <a href="./search.php" class="icon-search" title="検索">🔍</a>
            <a href="./contact.php" class="icon-contact" title="お問い合わせ">✉️</a>
        </nav>
    </header>
    <div class="backdrop" id="menuBackdrop"></div>
    <div class="menu_overlay" id="globalMenu" role="navigation" aria-hidden="true">
        <nav>
            <?php if (!empty($brands)): ?>
                <?php foreach ($brands as $brand): ?>
                    <a href="./search.php?brand=<?php echo $brand['id']; ?>" role="menuitem" class="brand">
                        <?php echo htmlspecialchars($brand['name']); ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="#" role="menuitem" class="brand brand1">ブランドA</a>
                <a href="#" role="menuitem" class="brand brand2">ブランドB</a>
                <a href="#" role="menuitem" class="brand brand3">ブランドC</a>
                <a href="#" role="menuitem" class="brand brand4">ブランドD</a>
            <?php endif; ?>
        </nav>
    </div>
    <div class="header_space"></div>
    <!-- headerここまで -->

    <main>
        <div id="scroll_contents">
            <div id="slideshow">
                <img src="../PHP/img/slide1.jpg" class="slide active">
                <img src="../PHP/img/slide2.jpg" class="slide">
                <img src="../PHP/img/slide3.jpg" class="slide">
            </div>

            <?php if (!empty($recentlyViewed)): ?>
                <h1>最近見たもの</h1>
                <div id="history">
                    <?php foreach ($recentlyViewed as $product): ?>
                        <div class="product_genre">
                            <a href="./product_detail.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo htmlspecialchars(getImagePath($product['image'], $product['brand_name'] ?? '')); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    onerror="this.src='../PHP/img/no-image.jpg'">
                                <div class="product_info">
                                    <p class="product_brand"><?php echo htmlspecialchars($product['brand_name'] ?? $product['category_name'] ?? 'カテゴリなし'); ?></p>
                                    <p class="product_name"><?php echo htmlspecialchars($product['name']); ?></p>
                                    <p class="product_price"><?php echo formatPrice($product['price']); ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h1>おすすめ商品</h1>
            <div id="recommend">
                <?php if (!empty($recommendedProducts)): ?>
                    <?php foreach ($recommendedProducts as $product): ?>
                        <div class="product_genre">
                            <a href="./product_detail.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo htmlspecialchars(getImagePath($product['image'], $product['brand_name'] ?? '')); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    onerror="this.src='../PHP/img/no-image.jpg'">
                                <div class="product_info">
                                    <p class="product_brand"><?php echo htmlspecialchars($product['brand_name'] ?? $product['category_name'] ?? 'カテゴリなし'); ?></p>
                                    <p class="product_name"><?php echo htmlspecialchars($product['name']); ?></p>
                                    <p class="product_price"><?php echo formatPrice($product['price']); ?></p>
                                    <p class="product_stock">在庫: <?php echo $product['stock'] ?? 0; ?>個</p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>現在おすすめ商品はありません。</p>
                <?php endif; ?>
            </div>

            <h1>お買い得商品</h1>
            <div id="sale">
                <?php if (!empty($saleProducts)): ?>
                    <?php foreach ($saleProducts as $product): ?>
                        <div class="product_genre sale_item">
                            <a href="./product_detail.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo htmlspecialchars(getImagePath($product['image'], $product['brand_name'] ?? '')); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    onerror="this.src='../PHP/img/no-image.jpg'">
                                <div class="product_info">
                                    <p class="product_brand"><?php echo htmlspecialchars($product['brand_name'] ?? $product['category_name'] ?? 'カテゴリなし'); ?></p>
                                    <p class="product_name"><?php echo htmlspecialchars($product['name']); ?></p>
                                    <p class="product_price"><?php echo formatPrice($product['price']); ?></p>
                                    <p class="product_stock">在庫: <?php echo $product['stock'] ?? 0; ?>個</p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>現在お買い得商品はありません。</p>
                <?php endif; ?>
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
    <!-- footer -->
    <script src="../JavaScript/hamburger.js"></script>
</body>

</html>