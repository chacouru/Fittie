<?php
session_start();
require_once './DbManager.php';
require_once './Encode.php';
require_once './ProductService.php';
require_once './config.php';
require_once __DIR__ . '/cart_button.php';

// エラーハンドリング用のカスタム例外クラス
class ProductException extends Exception {}

// レスポンス用のクラス
class ApiResponse {
    public bool $success;
    public array $data;
    public ?string $error;
    
    public function __construct(bool $success = true, array $data = [], ?string $error = null) {
        $this->success = $success;
        $this->data = $data;
        $this->error = $error;
    }
}

// ユーザーIDを取得（ログインしている場合）
$userId = $_SESSION['user_id'] ?? null;

// メインの処理を try-catch で囲む
try {
    $productService = new ProductService();
    
    // データを取得
    $recentlyViewed = $userId ? $productService->getRecentlyViewed($userId) : new ApiResponse(true, []);
    $recommendedProducts = $productService->getRecommendedProducts($userId);
    $saleProducts = $productService->getSaleProducts();
    $cheapProducts = $productService->getLowestPriceProducts();
    $brands = $productService->getBrands();
    
} catch (ProductException $e) {
    error_log("商品データ取得エラー: " . $e->getMessage());
    // ユーザー向けエラーメッセージ
    $errorMessage = "商品情報の読み込みに失敗しました。しばらく時間をおいてから再度お試しください。";
    
    // デフォルト値を設定
    $recentlyViewed = new ApiResponse(false, [], $errorMessage);
    $recommendedProducts = new ApiResponse(false, [], $errorMessage);
    $saleProducts = new ApiResponse(false, [], $errorMessage);
    $cheapProducts = new ApiResponse(false, [], $errorMessage);
    $brands = new ApiResponse(false, [], $errorMessage);
    
} catch (Exception $e) {
    error_log("予期しないエラー: " . $e->getMessage());
    $errorMessage = "申し訳ございません。システムエラーが発生しました。";
    
    // デフォルト値を設定
    $recentlyViewed = new ApiResponse(false, [], $errorMessage);
    $recommendedProducts = new ApiResponse(false, [], $errorMessage);
    $saleProducts = new ApiResponse(false, [], $errorMessage);
    $cheapProducts = new ApiResponse(false, [], $errorMessage);
    $brands = new ApiResponse(false, [], $errorMessage);
}

// ページメタ情報の設定
$pageTitle = "fitty. | フィットネス・スポーツ用品通販 - トップページ";
$pageDescription = "最新のフィットネス・スポーツ用品を豊富に取り揃えています。おすすめ商品やお買い得商品をチェック！";
$pageKeywords = "フィットネス,スポーツ用品,通販,トレーニング,お買い得";
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="description" content="<?php echo e($pageDescription); ?>">
    <meta name="keywords" content="<?php echo e($pageKeywords); ?>">
    
    <!-- プリロード -->
    <link rel="preload" href="../CSS/reset.css" as="style">
    <link rel="preload" href="../CSS/common.css" as="style">
    <link rel="preload" href="../CSS/index.css" as="style">
    
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/index.css">
    
    <!-- 構造化データ -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "fitty.",
        "description": "<?php echo e($pageDescription); ?>",
        "url": "<?php echo e($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
    }
    </script>
</head>

<body>
       <!-- headerここから -->
  <header class="header">
    <button class="menu_button" id="menuToggle" aria-label="メニューを開閉" aria-expanded="false" aria-controls="globalMenu"> <span class="bar"></span><span class="bar"></span><span class="bar"></span> </button>
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
        <!-- ローディング表示 -->
        <div id="loading" class="loading-overlay" style="display: none;" aria-hidden="true">
            <div class="loading-spinner"></div>
            <p>読み込み中...</p>
        </div>

        <div id="scroll_contents">
            <!-- スライドショー -->
            <section aria-label="メインスライドショー">
                <div id="slideshow">
                    <img src="../PHP/img/slideshow/slide1.jpg" class="slide active" alt="フィットネス商品のプロモーション" loading="eager">
                    <img src="../PHP/img/slideshow/slide2.jpg" class="slide" alt="スポーツウェアコレクション" loading="lazy">
                    <img src="../PHP/img/slideshow/slide3.jpg" class="slide" alt="トレーニング器具の特集" loading="lazy">
                </div>
            </section>

            <!-- 最近見た商品 -->
            <?php if ($recentlyViewed->success && !empty($recentlyViewed->data)): ?>
                <section aria-labelledby="recently-viewed-heading">
                    <h1 id="recently-viewed-heading">最近見た商品</h1>
                    <div id="history" class="product-grid">
                        <?php foreach ($recentlyViewed->data as $product): ?>
                            <?php echo renderProductCard($product, 'recently-viewed'); ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- おすすめ商品 -->
            <section aria-labelledby="recommended-heading">
                <h1 id="recommended-heading">おすすめ商品</h1>
                <?php if ($recommendedProducts->success && !empty($recommendedProducts->data)): ?>
                    <div id="recommend" class="product-grid">
                        <?php foreach ($recommendedProducts->data as $product): ?>
                            <?php echo renderProductCard($product, 'recommended'); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-products-message">
                        <?php if (!$recommendedProducts->success): ?>
                            <div class="error-message" role="alert">
                                <p><?php echo e($recommendedProducts->error ?? 'おすすめ商品の読み込みに失敗しました。'); ?></p>
                                <button onclick="location.reload()" class="btn-retry">再読み込み</button>
                            </div>
                        <?php else: ?>
                            <p>現在おすすめ商品はありません。</p>
                            <a href="./search.php" class="btn-primary">商品を探す</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- お買い得商品 -->
            <section aria-labelledby="sale-heading">
                <h1 id="sale-heading">お買い得商品</h1>
                <?php if ($saleProducts->success && !empty($saleProducts->data)): ?>
                    <div id="sale" class="product-grid">
                        <?php foreach ($saleProducts->data as $product): ?>
                            <?php echo renderProductCard($product, 'sale', true); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-products-message">
                        <?php if (!$saleProducts->success): ?>
                            <div class="error-message" role="alert">
                                <p><?php echo e($saleProducts->error ?? 'お買い得商品の読み込みに失敗しました。'); ?></p>
                                <button onclick="location.reload()" class="btn-retry">再読み込み</button>
                            </div>
                        <?php else: ?>
                            <p>現在お買い得商品はありません。</p>
                            <a href="./search.php" class="btn-primary">商品を探す</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="footer_container">
            <a href="index.php" aria-label="fitty.のトップページに戻る">
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
                <a href="#" aria-label="Twitterページ"><img src="icons/twitter.svg" alt="Twitter" loading="lazy"></a>
                <a href="#" aria-label="Instagramページ"><img src="icons/instagram.svg" alt="Instagram" loading="lazy"></a>
                <a href="#" aria-label="Facebookページ"><img src="icons/facebook.svg" alt="Facebook" loading="lazy"></a>
            </div>
            <div class="footer_copy">
                <small>&copy; 2025 Fitty All rights reserved.</small>
            </div>
        </div>
    </footer>

    <script src="../JavaScript/hamburger.js"></script>
    <script src="../JavaScript/productLoader.js"></script>
</body>
</html>

<?php
/**
 * 商品カードをレンダリングする関数
 */
function renderProductCard(array $product, string $context = '', bool $showSaleBadge = false): string {
    $productId = (int)$product['id'];
    $productName = e($product['name'] ?? '商品名不明');
    $productPrice = formatPrice($product['price'] ?? 0);
    $brandName = e($product['brand_name'] ?? $product['category_name'] ?? 'カテゴリなし');
    $stock = (int)($product['stock'] ?? 0);
    $imagePath = e(getImagePath($product['image'] ?? '', $product['brand_name'] ?? ''));
    $imageAlt = e($brandName . ' ' . $productName);
    
    $saleBadge = '';
    if ($showSaleBadge && isset($product['original_price']) && $product['original_price'] > $product['price']) {
        $discountRate = calculateDiscountRate($product['original_price'], $product['price']);
        $saleBadge = '<span class="sale-badge" aria-label="' . $discountRate . '%オフ">' . $discountRate . '%OFF</span>';
    }
    
    $stockStatus = '';
    if ($stock <= 0) {
        $stockStatus = '<span class="stock-out" aria-label="売り切れ">売り切れ</span>';
    } elseif ($stock <= 5) {
        $stockStatus = '<span class="stock-low" aria-label="残りわずか">残り' . $stock . '個</span>';
    }
    
    return <<<HTML
    <article class="product_genre" data-product-id="{$productId}" data-context="{$context}">
        <a href="./product_detail.php?id={$productId}" aria-label="{$productName}の詳細を見る">
            {$saleBadge}
            <div class="product-image-container">
                <img src="{$imagePath}" 
                     alt="{$imageAlt}" 
                     loading="lazy"
                     onerror="this.src='../PHP/img/no-image.jpg'; this.alt='画像が見つかりません';">
            </div>
            <div class="product_info">
                <p class="product_brand">{$brandName}</p>
                <h3 class="product_name">{$productName}</h3>
                <p class="product_price">{$productPrice}</p>
                {$stockStatus}
            </div>
        </a>
    </article>
HTML;
}

/**
 * 価格をフォーマットする関数（改善版）
 */
function formatPrice($price): string {
    if (!is_numeric($price) || $price < 0) {
        return '価格未定';
    }
    return number_format((int)$price) . '円';
}

/**
 * 画像パスを処理する関数（改善版）
 */
function getImagePath(string $imagePath, string $brandName = ''): string {
    if (empty($imagePath)) {
        return '../PHP/img/no-image.jpg';
    }

    // 絶対パスやURLの場合はそのまま返す
    if (str_starts_with($imagePath, 'http') || 
        str_starts_with($imagePath, '/') || 
        str_starts_with($imagePath, '../')) {
        return $imagePath;
    }

    // ブランド名が指定されている場合はブランドディレクトリを含める
    if (!empty($brandName)) {
        // ブランド名をURLセーフな形式に変換
        $safeBrandName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $brandName);
        return '../PHP/img/products/' . $safeBrandName . '/' . $imagePath;
    }

    // ブランド名がない場合は従来通り
    return '../PHP/img/products/' . $imagePath;
}

/**
 * 割引率を計算する関数（改善版）
 */
function calculateDiscountRate($originalPrice, $salePrice): int {
    if (!is_numeric($originalPrice) || !is_numeric($salePrice) || $originalPrice <= 0) {
        return 0;
    }
    
    $discount = (($originalPrice - $salePrice) / $originalPrice) * 100;
    return max(0, min(100, (int)round($discount)));
}
?>