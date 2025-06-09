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

    // 商品画像パスを生成（商品カード関数と同じ方式）
    $brand_name = isset($product['brand_name']) ? trim($product['brand_name']) : 'no-brand';
    $safe_brand_folder = preg_replace('/[^\w\-]/u', '_', $brand_name);
    $image_file = $product['image'] ?? 'no-image.png';
    $product_image_path = "../PHP/img/products/{$safe_brand_folder}/{$image_file}";

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
            
            if ($count >= 10) {
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

// 関連商品の画像パス生成関数
function getProductImagePath($product) {
    $brand_name = isset($product['brand_name']) ? trim($product['brand_name']) : 'no-brand';
    $safe_brand_folder = preg_replace('/[^\w\-]/u', '_', $brand_name);
    $image_file = $product['image'] ?? 'no-image.png';
    return "../PHP/img/products/{$safe_brand_folder}/{$image_file}";
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> | 商品詳細 | fitty.</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
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
            <img src="<?= htmlspecialchars($product_image_path) ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 onerror="this.src='../PHP/img/no-image.png'">
            <?php if ($product['is_on_sale']): ?>
                <div class="sale-label">SALE</div>
            <?php endif; ?>
            <?php if ($is_new): ?>
                <div class="new-label">NEW</div>
            <?php endif; ?>
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
                <span class="current-price">¥<?= number_format($display_price) ?></span>
                <?= $sale_info ?>
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
                    <?php
                    $related_image_path = getProductImagePath($related);
                    ?>
                    <div class="related_item">
                        <a href="product_detail.php?id=<?= $related['id'] ?>">
                            <img src="<?= htmlspecialchars($related_image_path) ?>" 
                                 alt="<?= htmlspecialchars($related['name']) ?>"
                                 onerror="this.src='../PHP/img/no-image.png'">
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