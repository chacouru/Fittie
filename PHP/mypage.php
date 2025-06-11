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
function isActive($currentTab, $tabName)
{
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
    <!-- headerここから -->
    <header class="header">
        <button class="menu_button" id="menuToggle" aria-label="メニューを開閉" aria-expanded="false" aria-controls="globalMenu">
            <span class="bar"></span><span class="bar"></span><span class="bar"></span>
        </button>
        <div class="header_logo">
            <h1><a href="./index.php">fitty.</a></h1>
        </div>
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
                                    } ?>
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
                    <h3>名前</h3>
                    <p><?= htmlspecialchars($user['name']) ?></p>
                    <h3>メールアドレス</h3>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                    <h3>住所</h3>
                    <p><?= htmlspecialchars($user['address']) ?></p>

                <?php elseif ($show === 'purchase'): ?>
                    <?php
                    // 購入履歴を取得する部分のコード（mypage.phpに組み込み用）

                    // データベースから実際の購入履歴を取得
                    $purchase_history = [];
                    if ($user_id) {
                        try {
                            $stmt = $pdo->prepare("
            SELECT 
                o.id as order_id,
                o.total_price,
                o.created_at,
                GROUP_CONCAT(p.name SEPARATOR ', ') as product_names,
                SUM(oi.quantity) as total_quantity
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE o.user_id = :user_id
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
                            $stmt->execute([':user_id' => $user_id]);
                            $purchase_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            // 購入履歴データを整形
                            foreach ($purchase_data as $order) {
                                // 配送状況をランダム生成（実際のプロジェクトでは別テーブルで管理）
                                $statuses = ['注文確認中', '準備中', '発送済み', '配達完了'];
                                $random_status = $statuses[array_rand($statuses)];

                                // 日付が新しいものほど「発送済み」以下のステータスになりやすく調整
                                $days_ago = (time() - strtotime($order['created_at'])) / (60 * 60 * 24);
                                if ($days_ago < 1) {
                                    $status = $statuses[0]; // 注文確認中
                                } elseif ($days_ago < 2) {
                                    $status = $statuses[1]; // 準備中
                                } elseif ($days_ago < 5) {
                                    $status = $statuses[2]; // 発送済み
                                } else {
                                    $status = $statuses[3]; // 配達完了
                                }

                                $purchase_history[] = [
                                    'id' => $order['order_id'],
                                    'date' => date('Y-m-d', strtotime($order['created_at'])),
                                    'amount' => $order['total_price'],
                                    'status' => $status,
                                    'products' => $order['product_names'] ?? '商品情報なし',
                                    'quantity' => $order['total_quantity'] ?? 0
                                ];
                            }
                        } catch (PDOException $e) {
                            error_log("購入履歴取得エラー: " . $e->getMessage());
                            $purchase_history = [];
                        }
                    }

                    // 購入履歴詳細を取得する関数
                    function getPurchaseDetails($pdo, $order_id)
                    {
                        try {
                            $stmt = $pdo->prepare("
            SELECT 
                oi.quantity,
                oi.price,
                p.name as product_name,
                p.image_url,
                b.name as brand_name
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE oi.order_id = :order_id
        ");
                            $stmt->execute([':order_id' => $order_id]);
                            return $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            error_log("購入詳細取得エラー: " . $e->getMessage());
                            return [];
                        }
                    }
                    ?>

                    <!-- 購入履歴表示部分のHTML -->
                    <?php if ($show === 'purchase'): ?>
                        <h2>購入履歴</h2>
                        <?php if (empty($purchase_history)): ?>
                            <p class="no-history">購入履歴がありません。</p>
                        <?php else: ?>
                            <div class="purchase-history">
                                <?php foreach ($purchase_history as $purchase): ?>
                                    <div class="purchase-item">
                                        <div class="purchase-header">
                                            <div class="purchase-info">
                                                <h3>注文番号: <?= htmlspecialchars($purchase['id']) ?></h3>
                                                <p class="purchase-date">注文日: <?= htmlspecialchars($purchase['date']) ?></p>
                                                <p class="purchase-amount">合計金額: ¥<?= number_format($purchase['amount']) ?></p>
                                            </div>
                                            <div class="purchase-status">
                                                <span class="status <?= getStatusClass($purchase['status']) ?>">
                                                    <?= htmlspecialchars($purchase['status']) ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="purchase-summary">
                                            <p class="item-summary">
                                                商品数: <?= $purchase['quantity'] ?>点 |
                                                <?= strlen($purchase['products']) > 50 ?
                                                    htmlspecialchars(substr($purchase['products'], 0, 50)) . '...' :
                                                    htmlspecialchars($purchase['products']) ?>
                                            </p>
                                        </div>

                                        <div class="purchase-actions">
                                            <button class="detail-btn" onclick="toggleDetails(<?= $purchase['id'] ?>)">
                                                詳細を見る
                                            </button>
                                            <?php if ($purchase['status'] === '配達完了'): ?>
                                                <button class="review-btn">レビューを書く</button>
                                            <?php endif; ?>
                                        </div>

                                        <div class="purchase-details" id="details-<?= $purchase['id'] ?>" style="display: none;">
                                            <h4>注文詳細</h4>
                                            <div class="order-items">
                                                <?php
                                                $details = getPurchaseDetails($pdo, $purchase['id']);
                                                foreach ($details as $item):
                                                ?>
                                                    <div class="order-item">
                                                        <div class="item-image">
                                                            <?php if (!empty($item['image_url'])): ?>
                                                                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                                            <?php else: ?>
                                                                <div class="no-image">画像なし</div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="item-info">
                                                            <p class="item-brand"><?= htmlspecialchars($item['brand_name'] ?? 'ブランド不明') ?></p>
                                                            <p class="item-name"><?= htmlspecialchars($item['product_name']) ?></p>
                                                            <p class="item-price">¥<?= number_format($item['price']) ?> × <?= $item['quantity'] ?>個</p>
                                                            <p class="item-subtotal">小計: ¥<?= number_format($item['price'] * $item['quantity']) ?></p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php
                    // ステータスに応じたCSSクラスを返す関数
                    function getStatusClass($status)
                    {
                        switch ($status) {
                            case '注文確認中':
                                return 'status-pending';
                            case '準備中':
                                return 'status-preparing';
                            case '発送済み':
                                return 'status-shipped';
                            case '配達完了':
                                return 'status-delivered';
                            default:
                                return 'status-unknown';
                        }
                    }
                    ?>

                    <script>
                        // 詳細表示の切り替え
                        function toggleDetails(orderId) {
                            const details = document.getElementById('details-' + orderId);
                            const btn = event.target;

                            if (details.style.display === 'none') {
                                details.style.display = 'block';
                                btn.textContent = '詳細を閉じる';
                            } else {
                                details.style.display = 'none';
                                btn.textContent = '詳細を見る';
                            }
                        }
                    </script>

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
                            <?php foreach ($all_brands as $brands): ?>
                                <label>
                                    <input type="checkbox" name="brands[]" value="<?= $brands['id'] ?>" <?= in_array($brands['id'], $user_brands) ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($brands['name']) ?>
                                </label><br>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="submit-button">更新する</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- フッターここから -->
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

    <script src="../JavaScript/menu.js"></script>
    <script src="../JavaScript/hamburger.js"></script>

</body>

</html>