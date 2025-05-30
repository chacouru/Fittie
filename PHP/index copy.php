<?php
require_once __DIR__ . '/login_function/session.php';

// データベース接続設定
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

// ログインユーザーの最近見た商品を取得
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

// おすすめ商品を取得（レーティング順、在庫あり、アクティブな商品）
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, b.name as brand_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    WHERE p.is_active = 1 AND p.stock > 0
    ORDER BY p.rating DESC, p.review_count DESC, p.created_at DESC
    LIMIT 10
");
$stmt->execute();
$recommended_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// セール商品を取得
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, b.name as brand_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    WHERE p.is_active = 1 AND p.is_on_sale = 1 AND p.stock > 0
    ORDER BY 
        CASE 
            WHEN p.sale_price IS NOT NULL AND p.sale_price > 0 
            THEN ((p.price - p.sale_price) / p.price) * 100 
            ELSE 0 
        END DESC
    LIMIT 10
");
$stmt->execute();
$sale_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ブランド一覧を取得
$stmt = $pdo->prepare("SELECT * FROM brands ORDER BY name");
$stmt->execute();
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 商品カード表示用の関数
function displayProductCard($product) {
    $image_path = !empty($product['image']) ? "../PHP/img/" . $product['image'] : "../PHP/img/no-image.jpg";
    $brand_name = $product['brand_name'] ?? 'ブランド不明';
    $category_name = $product['category_name'] ?? 'カテゴリ不明';
    
    // セール価格の計算
    $display_price = $product['price'];
    $sale_info = '';
    if ($product['is_on_sale'] && $product['sale_price'] && $product['sale_price'] > 0) {
        $display_price = $product['sale_price'];
        $discount_rate = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
        $sale_info = "<span class='original-price'>¥" . number_format($product['price']) . "</span><span class='sale-badge'>{$discount_rate}%OFF</span>";
    }
    
    // レーティング表示
    $rating_stars = '';
    if ($product['rating'] > 0) {
        $full_stars = floor($product['rating']);
        $half_star = ($product['rating'] - $full_stars) >= 0.5 ? 1 : 0;
        
        for ($i = 0; $i < $full_stars; $i++) {
            $rating_stars .= '★';
        }
        if ($half_star) {
            $rating_stars .= '☆';
        }
        $rating_stars .= " ({$product['rating']}) ({$product['review_count']}件)";
    }
    
    echo "<div class='product-card' data-product-id='{$product['id']}'>";
    echo "<div class='product-image'>";
    echo "<img src='{$image_path}' alt='{$product['name']}' onerror=\"this.src='../PHP/img/no-image.jpg'\">";
    if ($product['is_on_sale']) {
        echo "<div class='sale-label'>SALE</div>";
    }
    echo "</div>";
    echo "<div class='product-info'>";
    echo "<div class='product-brand'>{$brand_name}</div>";
    echo "<div class='product-name'>{$product['name']}</div>";
    echo "<div class='product-category'>{$category_name}</div>";
    if ($rating_stars) {
        echo "<div class='product-rating'>{$rating_stars}</div>";
    }
    echo "<div class='product-price'>";
    echo "<span class='current-price'>¥" . number_format($display_price) . "</span>";
    echo $sale_info;
    echo "</div>";
    echo "<div class='product-stock'>在庫: {$product['stock']}個</div>";
    echo "</div>";
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
    <style>
        /* スライドショー用のスタイル */
        #slideshow {
            position: relative;
            width: 100%;
            height: 400px;
            overflow: hidden;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .slide-container {
            position: relative;
            width: 100%;
            height: 100%;
        }
        
        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }
        
        .slide.active {
            opacity: 1;
        }
        
        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
        }
        
        .slide-content {
            position: relative;
            z-index: 2;
            color: white;
            padding: 0 60px;
            max-width: 500px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        }
        
        .slide-content h2 {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .slide-content p {
            font-size: 18px;
            margin-bottom: 25px;
            line-height: 1.4;
        }
        
        .slide-btn {
            display: inline-block;
            background: rgba(255,255,255,0.9);
            color: #333;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .slide-btn:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .slide-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.8);
            border: none;
            font-size: 24px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 3;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .slide-nav:hover {
            background: rgba(255,255,255,0.95);
            transform: translateY(-50%) scale(1.1);
        }
        
        .slide-nav.prev {
            left: 20px;
        }
        
        .slide-nav.next {
            right: 20px;
        }
        
        .slide-dots {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 3;
        }
        
        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.5);
            background: transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .dot.active,
        .dot:hover {
            background: white;
            border-color: white;
        }
        
        /* レスポンシブ対応 */
        @media (max-width: 768px) {
            #slideshow {
                height: 300px;
            }
            
            .slide-content {
                padding: 0 30px;
                max-width: 350px;
            }
            
            .slide-content h2 {
                font-size: 24px;
            }
            
            .slide-content p {
                font-size: 16px;
            }
            
            .slide-nav {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
            
            .slide-nav.prev {
                left: 15px;
            }
            
            .slide-nav.next {
                right: 15px;
            }
        }
        
        /* 商品カード用の追加スタイル */
        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            background: white;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .sale-label {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #ff4444;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .product-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-brand {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .product-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .product-category {
            font-size: 12px;
            color: #888;
            margin-bottom: 8px;
        }
        
        .product-rating {
            font-size: 14px;
            color: #ffa500;
            margin-bottom: 10px;
        }
        
        .product-price {
            margin-bottom: 8px;
        }
        
        .current-price {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .original-price {
            font-size: 14px;
            color: #999;
            text-decoration: line-through;
            margin-left: 8px;
        }
        
        .sale-badge {
            background: #ff4444;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 8px;
        }
        
        .product-stock {
            font-size: 12px;
            color: #666;
            margin-top: auto;
        }
        
        #history, #recommend, #sale {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .no-products {
            text-align: center;
            color: #666;
            padding: 40px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: bold;
            margin: 40px 0 20px 0;
            color: #333;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
    </style>
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
                        <img src="../PHP/img/slide3.jpg" alt="新着アイテム">
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

            <?php if (isset($_SESSION['user_id']) && !empty($recent_products)): ?>
                <h1 class="section-title">最近見たもの</h1>
                <div id="history">
                    <?php foreach ($recent_products as $product): ?>
                        <?php displayProductCard($product); ?>
                    <?php endforeach; ?>
                </div>
            <?php elseif (isset($_SESSION['user_id'])): ?>
                <h1 class="section-title">最近見たもの</h1>
                <div class="no-products">
                    まだ商品を閲覧していません。商品を見て回ってみましょう！
                </div>
            <?php endif; ?>

            <h1 class="section-title">おすすめ商品</h1>
            <div id="recommend">
                <?php if (!empty($recommended_products)): ?>
                    <?php foreach ($recommended_products as $product): ?>
                        <?php displayProductCard($product); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">現在おすすめ商品はありません</div>
                <?php endif; ?>
            </div>

            <?php if (!empty($sale_products)): ?>
                <h1 class="section-title">セール商品</h1>
                <div id="sale">
                    <?php foreach ($sale_products as $product): ?>
                        <?php displayProductCard($product); ?>
                    <?php endforeach; ?>
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
    <script>
        // 商品カードクリック時の処理
        document.addEventListener('DOMContentLoaded', function() {
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                card.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    if (productId) {
                        // 商品詳細ページに遷移
                        window.location.href = `./product_detail.php?id=${productId}`;
                    }
                });
            });
        });

        // スライドショーの制御
        let currentSlideIndex = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        let slideInterval;
        
        function showSlide(index) {
            // 全てのスライドを非アクティブに
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            // 指定されたスライドをアクティブに
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            
            currentSlideIndex = index;
        }
        
        function nextSlide() {
            const nextIndex = (currentSlideIndex + 1) % slides.length;
            showSlide(nextIndex);
        }
        
        function prevSlide() {
            const prevIndex = (currentSlideIndex - 1 + slides.length) % slides.length;
            showSlide(prevIndex);
        }
        
        function currentSlide(index) {
            showSlide(index - 1);
            // 手動操作時は自動スライドを一時停止し、3秒後に再開
            clearInterval(slideInterval);
            startAutoSlide();
        }
        
        function startAutoSlide() {
            slideInterval = setInterval(nextSlide, 5000); // 5秒間隔
        }
        
        // 自動スライド開始
        startAutoSlide();
        
        // スライドショーにマウスが乗った時は自動スライドを停止
        const slideshow = document.getElementById('slideshow');
        slideshow.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
        });
        
        // マウスが離れた時は自動スライドを再開
        slideshow.addEventListener('mouseleave', () => {
            startAutoSlide();
        });
    </script>
</body>
</html>