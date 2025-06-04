<?php
session_start();
require_once 'db_connect.php';

$brands = [];
$user_id = null;

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

try {
    // 商品IDを取得（GETパラメータ）
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // IDが正しくない場合は終了
    if ($id <= 0) {
        throw new Exception("不正な商品IDです。");
    }

    // 商品詳細を取得（ブランド名とカテゴリ名も含む）
    $stmt = $pdo->prepare("
        SELECT p.*, b.name as brand_name, c.name as category_name
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.is_active = 1
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("商品が見つかりませんでした。");
    }

    // 閲覧履歴を記録（ログインユーザーのみ）
    if ($user_id) {
        // 既存の履歴をチェック
        $stmt = $pdo->prepare("SELECT id FROM view_history WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // 既存の履歴がある場合は閲覧日時を更新
            $stmt = $pdo->prepare("UPDATE view_history SET viewed_at = NOW() WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $id]);
        } else {
            // 新規追加の場合、まず履歴数をチェック
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM view_history WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $count = $stmt->fetchColumn();
            
            if ($count >= 5) {
                // 5件以上ある場合、最も古い履歴を削除
                $stmt = $pdo->prepare("
                    DELETE FROM view_history 
                    WHERE user_id = ? 
                    ORDER BY viewed_at ASC 
                    LIMIT 1
                ");
                $stmt->execute([$user_id]);
            }
            
            // 新しい履歴を追加
            $stmt = $pdo->prepare("INSERT INTO view_history (user_id, product_id, viewed_at) VALUES (?, ?, NOW())");
            $stmt->execute([$user_id, $id]);
        }
    }

    // 関連商品を取得（同じカテゴリの他の商品、最大4件）
    $related_products = [];
    if ($product['category_id']) {
        $stmt = $pdo->prepare("
            SELECT p.*, b.name as brand_name
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
            ORDER BY p.created_at DESC
            LIMIT 4
        ");
        $stmt->execute([$product['category_id'], $id]);
        $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}

// カートに追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!$user_id) {
        $error_message = "カートに追加するにはログインが必要です。";
    } else {
        try {
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            
            // 在庫チェック
            if ($quantity > $product['stock']) {
                $error_message = "在庫が足りません。";
            } else {
                // カートに追加（既に同じ商品がある場合は数量を更新）
                $stmt = $pdo->prepare("
                    INSERT INTO cart_items (user_id, product_id, quantity) 
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
                ");
                $stmt->execute([$user_id, $id, $quantity]);
                $success_message = "商品をカートに追加しました。";
            }
        } catch (Exception $e) {
            $error_message = "カートへの追加に失敗しました。";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> | 商品詳細 | fitty.</title>
    <link rel="stylesheet" href="../CSS/products.css">
    <style>
        .product_detail_container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .product_main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .product_image {
            text-align: center;
        }
        
        .product_image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .product_info {
            padding: 20px 0;
        }
        
        .product_info h1 {
            font-size: 2.2em;
            margin-bottom: 10px;
            color: #333;
        }
        
        .product_meta {
            margin-bottom: 20px;
            font-size: 0.9em;
            color: #666;
        }
        
        .product_meta span {
            margin-right: 15px;
        }
        
        .price {
            font-size: 2em;
            color: #e74c3c;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .sale_price {
            color: #27ae60;
        }
        
        .original_price {
            text-decoration: line-through;
            color: #999;
            font-size: 0.8em;
            margin-left: 10px;
        }
        
        .stock_info {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .stock_low {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock_out {
            background: #f8d7da;
            color: #721c24;
        }
        
        .description {
            line-height: 1.6;
            margin: 20px 0;
            color: #555;
        }
        
        .rating {
            margin: 15px 0;
        }
        
        .stars {
            color: #ffc107;
            margin-right: 10px;
        }
        
        .add_to_cart_form {
            margin: 30px 0;
        }
        
        .quantity_selector {
            margin: 15px 0;
        }
        
        .quantity_selector label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .quantity_selector input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 80px;
        }
        
        .add_to_cart_btn {
            background: #007bff;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 4px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .add_to_cart_btn:hover {
            background: #0056b3;
        }
        
        .add_to_cart_btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .message {
            padding: 10px;
            margin: 15px 0;
            border-radius: 4px;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .related_products {
            margin-top: 50px;
        }
        
        .related_products h2 {
            margin-bottom: 30px;
            color: #333;
        }
        
        .related_grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .related_item {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s;
        }
        
        .related_item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .related_item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        
        .related_item h3 {
            margin: 10px 0;
            font-size: 1.1em;
        }
        
        .related_item .price {
            font-size: 1.2em;
            margin: 5px 0;
        }
        
        @media (max-width: 768px) {
            .product_main {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .product_info h1 {
                font-size: 1.8em;
            }
            
            .price {
                font-size: 1.5em;
            }
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

<div class="product_detail_container">
    <!-- パンくずナビ -->
    <nav aria-label="breadcrumb" style="margin-bottom: 20px;">
        <ol style="display: flex; list-style: none; padding: 0; color: #666;">
            <li><a href="index.php" style="color: #007bff; text-decoration: none;">ホーム</a></li>
            <li style="margin: 0 10px;">&gt;</li>
            <?php if ($product['category_name']): ?>
                <li><a href="category.php?id=<?= $product['category_id'] ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($product['category_name']) ?></a></li>
                <li style="margin: 0 10px;">&gt;</li>
            <?php endif; ?>
            <li style="color: #333;"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <div class="product_main">
        <div class="product_image">
            <img src="./img/products/<?= htmlspecialchars($product['image']) ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        
        <div class="product_info">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="product_meta">
                <?php if ($product['brand_name']): ?>
                    <span><strong>ブランド:</strong> <?= htmlspecialchars($product['brand_name']) ?></span>
                <?php endif; ?>
                <?php if ($product['category_name']): ?>
                    <span><strong>カテゴリ:</strong> <?= htmlspecialchars($product['category_name']) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($product['rating'] > 0): ?>
                <div class="rating">
                    <span class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= $i <= $product['rating'] ? '★' : '☆' ?>
                        <?php endfor; ?>
                    </span>
                    <span><?= $product['rating'] ?> (<?= $product['review_count'] ?>件のレビュー)</span>
                </div>
            <?php endif; ?>

            <div class="price">
                <?php if ($product['is_on_sale'] && $product['sale_price']): ?>
                    <span class="sale_price">¥<?= number_format($product['sale_price']) ?></span>
                    <span class="original_price">¥<?= number_format($product['price']) ?></span>
                <?php else: ?>
                    ¥<?= number_format($product['price']) ?>
                <?php endif; ?>
            </div>

            <div class="stock_info <?= $product['stock'] <= 0 ? 'stock_out' : ($product['stock'] <= 5 ? 'stock_low' : '') ?>">
                <?php if ($product['stock'] <= 0): ?>
                    <strong>在庫切れ</strong>
                <?php elseif ($product['stock'] <= 5): ?>
                    <strong>残り<?= $product['stock'] ?>点</strong> - お急ぎください
                <?php else: ?>
                    <strong>在庫あり</strong>
                <?php endif; ?>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="message success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="message error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form class="add_to_cart_form" method="POST">
                <div class="quantity_selector">
                    <label for="quantity">数量:</label>
                    <input type="number" 
                           id="quantity" 
                           name="quantity" 
                           value="1" 
                           min="1" 
                           max="<?= $product['stock'] ?>"
                           <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                </div>
                
                <button type="submit" 
                        name="add_to_cart" 
                        class="add_to_cart_btn"
                        <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                    <?= $product['stock'] <= 0 ? '在庫切れ' : 'カートに追加' ?>
                </button>
            </form>

            <div class="description">
                <h3>商品説明</h3>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>
        </div>
    </div>

    <?php if (!empty($related_products)): ?>
        <div class="related_products">
            <h2>関連商品</h2>
            <div class="related_grid">
                <?php foreach ($related_products as $related): ?>
                    <div class="related_item">
                        <a href="product_detail.php?id=<?= $related['id'] ?>">
                            <img src="./img/products/<?= htmlspecialchars($related['image']) ?>" 
                                 alt="<?= htmlspecialchars($related['name']) ?>">
                            <h3><?= htmlspecialchars($related['name']) ?></h3>
                            <?php if ($related['brand_name']): ?>
                                <p style="color: #666; font-size: 0.9em;"><?= htmlspecialchars($related['brand_name']) ?></p>
                            <?php endif; ?>
                            <div class="price">¥<?= number_format($related['price']) ?></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// メニュートグル機能
document.getElementById('menuToggle')?.addEventListener('click', function() {
    const menu = document.getElementById('globalMenu');
    const backdrop = document.getElementById('menuBackdrop');
    const isOpen = this.getAttribute('aria-expanded') === 'true';
    
    this.setAttribute('aria-expanded', !isOpen);
    menu.setAttribute('aria-hidden', isOpen);
    
    if (!isOpen) {
        menu.style.display = 'block';
        backdrop.style.display = 'block';
        document.body.style.overflow = 'hidden';
    } else {
        menu.style.display = 'none';
        backdrop.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});

// 背景クリックでメニューを閉じる
document.getElementById('menuBackdrop')?.addEventListener('click', function() {
    const menuToggle = document.getElementById('menuToggle');
    const menu = document.getElementById('globalMenu');
    
    menuToggle.setAttribute('aria-expanded', 'false');
    menu.setAttribute('aria-hidden', 'true');
    menu.style.display = 'none';
    this.style.display = 'none';
    document.body.style.overflow = 'auto';
});
</script>

</body>
</html>