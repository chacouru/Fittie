<?php
// mypage.php
require_once './login_function/functions.php';
require_once 'db_connect.php';

$user_id = check_login(); // 先にログインチェックして user_id を取得
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
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ユーザー情報取得
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo 'ユーザー情報が見つかりません。';
    exit;
}

// 全ブランド + ユーザーが選択しているブランドID（設定用チェックボックス）
$all_brands = [];
$user_brands = [];

if ($user_id) {
    $stmt = $pdo->query("SELECT * FROM brands");
    $all_brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT brand_id FROM favorite_brands WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $user_brands = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'brand_id');
}

// 仮の購入履歴
$purchase_history = [
    ['id' => '10001', 'date' => '2025-05-10', 'amount' => 5800, 'status' => '発送済み'],
    ['id' => '10002', 'date' => '2025-05-05', 'amount' => 3200, 'status' => '配達完了'],
    ['id' => '10003', 'date' => '2025-04-28', 'amount' => 12000, 'status' => '配達完了']
];

$show = $_GET['show'] ?? 'profile';
function isActive($currentTab, $tabName) {
    return $currentTab === $tabName ? 'active-tab' : '';
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>fitty. | マイページ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/mypage.css">
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
            <p style="padding: 10px;">お気に入りのブランドが登録されていません。</p>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>

<div class="header_space"></div>

<div class="mypage-container">
    <h1 class="page-title">マイページ</h1>
    <div class="profile-section">
        <div class="profile-photo"></div>
        <div class="profile-info">
            <div class="id-display">ID: <?= htmlspecialchars($user['id']) ?></div>
            <div class="name-display">名前: <?= htmlspecialchars($user['name']) ?></div>
        </div>
        <div class="action-buttons">
            <button class="action-button <?= isActive($show, 'profile') ?>" onclick="location.href='?show=profile'">プロフィール</button>
            <button class="action-button <?= isActive($show, 'purchase') ?>" onclick="location.href='?show=purchase'">購入履歴</button>
            <button class="action-button <?= isActive($show, 'settings') ?>" onclick="location.href='?show=settings'">設定</button>
        </div>
        <div class="user-details">
            <?php if ($show === 'profile'): ?>
                <h2>プロフィール情報</h2>
                <h3>名前</h3><p><?= htmlspecialchars($user['name']) ?></p>
                <h3>メールアドレス</h3><p><?= htmlspecialchars($user['email']) ?></p>
                <h3>住所</h3><p><?= htmlspecialchars($user['address']) ?></p>

            <?php elseif ($show === 'purchase'): ?>
                <h2>購入履歴</h2>
                <table class="purchase-table">
                    <thead><tr><th>注文番号</th><th>日付</th><th>金額</th><th>状態</th></tr></thead>
                    <tbody>
                    <?php foreach ($purchase_history as $purchase): ?>
                        <tr>
                            <td><?= htmlspecialchars($purchase['id']) ?></td>
                            <td><?= htmlspecialchars($purchase['date']) ?></td>
                            <td>¥<?= number_format($purchase['amount']) ?></td>
                            <td><span class="status <?= $purchase['status'] === '発送済み' ? 'status-shipped' : 'status-delivered' ?>">
                                <?= htmlspecialchars($purchase['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($show === 'settings'): ?>
                <h2>アカウント設定</h2>
                <form method="post" action="update_profile.php">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
                    <div class="form-group">
                        <h3>名前</h3>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>">
                    </div>
                    <div class="form-group">
                        <h3>メールアドレス</h3>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                    <div class="form-group">
                        <h3>住所</h3>
                        <textarea name="address" class="form-control"><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>
                    <h3>お気に入りブランド</h3>
                    <div class="brand-checkboxes">
                        <?php foreach ($all_brands as $brand): ?>
                            <label>
                                <input type="checkbox" name="brands[]" value="<?= $brand['id'] ?>" <?= in_array($brand['id'], $user_brands) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($brand['name']) ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="submit-button">更新する</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="footer_container">
        <a href="index.php"><div class="footer_logo"><h2>fitty.</h2></div></a>
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

<script src="../JavaScript/menu.js"></script>
<script src="../JavaScript/hamburger.js"></script>

</body>
</html>
