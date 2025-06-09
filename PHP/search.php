<?php
session_start();
require_once 'db_connect.php';
require_once 'cart_button.php'; // cart_button.php読み込み

$brands = [];
$genres = [];
$user_id = null;

// 1. ログイン中ユーザーのお気に入りブランド取得
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

// 2. ブランド一覧を取得（検索フォーム用）
$stmt = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC");
$all_brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. ジャンル一覧を取得（検索フォーム用）
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$genres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. 検索処理
$results = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['brand']) || isset($_GET['genre']))) {
    $conditions = [];
    $params = [];

    if (!empty($_GET['brand'])) {
        $conditions[] = 'p.brand_id = ?';
        $params[] = $_GET['brand'];
    }

    if (!empty($_GET['genre'])) {
        $conditions[] = 'p.category_id = ?';
        $params[] = $_GET['genre'];
    }

    // 在庫数(stock)も取得
    $sql = "
        SELECT p.*, b.name AS brand_name, c.name AS category_name
        FROM products p
        JOIN brands b ON p.brand_id = b.id
        JOIN categories c ON p.category_id = c.id
    ";

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>fitty. | 探す</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/search.css">
</head>
<body>
<!-- header -->
<header class="header">
    <button class="menu_button" id="menuToggle" aria-label="メニューを開閉">
        <span class="bar"></span><span class="bar"></span><span class="bar"></span>
    </button>
    <div class="header_logo">
        <h1><a href="./index.php">fitty.</a></h1>
    </div>
    <nav class="header_nav">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="login_logout_img">
                <a href="logout.php"><img src="./img/logout.jpg" alt="ログアウト"></a>
            </div>
        <?php else: ?>
            <div class="login_logout_img">
                <a href="login.php"><img src="./img/login.png" alt="ログイン"></a>
            </div>
        <?php endif; ?>
        <a href="./mypage.php">👤</a>
        <a href="./cart.php">🛒</a>
        <a href="./search.php">🔍</a>
        <a href="./contact.php">✉️</a>
    </nav>
</header>

<!-- ハンバーガーメニュー -->
<div class="backdrop" id="menuBackdrop"></div>
<?php if ($user_id): ?>
<div class="menu_overlay" id="globalMenu" role="navigation">
    <nav>
        <?php if (!empty($brands)): ?>
            <?php foreach ($brands as $index => $brand): ?>
                <a href="brand.php?id=<?= htmlspecialchars($brand['id']) ?>"
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

<!-- 検索フォーム -->
<main>
    <form action="" method="get" class="form_box">
        <h1>探す</h1>
        <div class="form_container">
            <div class="select_container">
                <select name="brand" required>
                    <option value="" selected disabled hidden>ブランド</option>
                    <?php foreach ($all_brands as $brand_option): ?>
                        <option value="<?= htmlspecialchars($brand_option['id']) ?>"
                            <?= (isset($_GET['brand']) && $_GET['brand'] == $brand_option['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($brand_option['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="select_container">
                <select name="genre" required>
                    <option value="" selected disabled hidden>ジャンル</option>
                    <?php foreach ($genres as $genre_option): ?>
                        <option value="<?= htmlspecialchars($genre_option['id']) ?>"
                            <?= (isset($_GET['genre']) && $_GET['genre'] == $genre_option['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($genre_option['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="button_container">
            <button type="submit">この条件で探す</button>
            <input type="reset" value="リセット" onclick="window.location='search.php'">
        </div>
    </form>

<?php
$searched = ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['brand']) || isset($_GET['genre'])));
?>

<!-- 検索結果表示 -->
<?php if (!empty($results)): ?>
    <section class="results">
        <h2>検索結果</h2>
        <ul>
            <?php foreach ($results as $product): ?>
                <?php
                $brand_folder = $product['brand_name'];
                $image_file = $product['image'];
                $image_path = "./img/products/" . rawurlencode($brand_folder) . "/" . rawurlencode($image_file);

                $stock = isset($product['stock']) ? (int)$product['stock'] : 10; // stockカラムが無ければ10固定
                ?>
<li>
    <a href="product_detail.php?id=<?= $product['id'] ?>">
        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($product['name']) ?>" width="100">
        <p>商品名：<?= htmlspecialchars($product['name']) ?></p>
    </a>
    <p>ブランド：<?= htmlspecialchars($product['brand_name']) ?></p>
    <p>ジャンル：<?= htmlspecialchars($product['category_name']) ?></p>
    <p>価格：<?= htmlspecialchars($product['price']) ?>円</p>

    <!-- カート追加ボタン -->
    <?php displayCartButton($product['id'], $product['name'], $stock, $product['price']); ?>
</li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php elseif ($searched): ?>
    <p>該当する商品は見つかりませんでした。</p>
<?php endif; ?>

</main>

<!-- footer -->
<footer class="footer">
    <div class="footer_container">
        <a href="index.php">
            <div class="footer_logo"><h2>fitty.</h2></div>
        </a>
        <div class="footer_links">
            <a href="./overview.php">会社概要</a>
            <a href="./terms.php">利用規約</a>
            <a href="./privacy.php">プライバシーポリシー</a>
        </div>
        <div class="footer_sns">
            <a href="#"><img src="icons/twitter.svg" alt="Twitter"></a>
            <a href="#"><img src="icons/instagram.svg" alt="Instagram"></a>
            <a href="#"><img src="icons/facebook.svg" alt="Facebook"></a>
        </div>
        <div class="footer_copy">
            <small>&copy; 2025 Fitty All rights reserved.</small>
        </div>
    </div>
</footer>

<script src="../JavaScript/hamburger.js"></script>
</body>
</html>
