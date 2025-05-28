<?php
// mypage.php
require_once 'db_connect.php';
require_once './login_function/functions.php';

$user_id = check_login();

// ユーザー情報をデータベースから取得
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo 'ユーザー情報が見つかりません。';
    exit;
}

// 購入履歴（仮のデータ）
$purchase_history = [
    ['id' => '10001', 'date' => '2025-05-10', 'amount' => 5800, 'status' => '発送済み'],
    ['id' => '10002', 'date' => '2025-05-05', 'amount' => 3200, 'status' => '配達完了'],
    ['id' => '10003', 'date' => '2025-04-28', 'amount' => 12000, 'status' => '配達完了']
];

// 表示する内容をリクエストパラメータから取得（デフォルトはユーザー情報）
$show = isset($_GET['show']) ? $_GET['show'] : 'profile';

function isActive($currentTab, $tabName) {
    return $currentTab === $tabName ? 'active-tab' : '';
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/mypage.css">
    <title>fitty. | マイページ</title>
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
            <a href="#" role="menuitem" class="bland brand1">ブランドA</a>
            <a href="#" role="menuitem" class="bland brand2">ブランドB</a>
            <a href="#" role="menuitem" class="bland brand3">ブランドC</a>
            <a href="#" role="menuitem" class="bland brand4">ブランドD</a>
        </nav>
    </div>
    <div class="header_space"></div>
    <!-- headerここまで -->

    <div class="mypage-container">
        <h1 class="page-title">マイページ</h1>

        <div class="profile-section">
            <div class="profile-photo"></div>

            <div class="profile-info">
                <div class="id-display">ID: <?php echo htmlspecialchars($user['id']); ?></div>
                <div class="name-display">名前: <?php echo htmlspecialchars($user['name']); ?></div>
            </div>

            <div class="action-buttons">
                <button class="action-button <?php echo isActive($show, 'profile'); ?>" onclick="location.href='?show=profile'">プロフィール</button>
                <button class="action-button <?php echo isActive($show, 'purchase'); ?>" onclick="location.href='?show=purchase'">購入履歴</button>
                <button class="action-button <?php echo isActive($show, 'settings'); ?>" onclick="location.href='?show=settings'">設定</button>
            </div>

            <div class="user-details">
                <?php if ($show === 'profile'): ?>
                    <h2>プロフィール情報</h2>
                    <h3>名前</h3>
                    <p><?php echo htmlspecialchars($user['name']); ?></p>

                    <h3>メールアドレス</h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>

                    <h3>住所</h3>
                    <p><?php echo htmlspecialchars($user['address']); ?></p>

                <?php elseif ($show === 'purchase'): ?>
                    <h2>購入履歴</h2>
                    <p>これまでのご注文履歴です。</p>

                    <table class="purchase-table">
                        <thead>
                            <tr>
                                <th>注文番号</th>
                                <th>日付</th>
                                <th>金額</th>
                                <th>状態</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchase_history as $purchase): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($purchase['id']); ?></td>
                                <td><?php echo htmlspecialchars($purchase['date']); ?></td>
                                <td>¥<?php echo number_format($purchase['amount']); ?></td>
                                <td>
                                    <?php
                                    $statusClass = $purchase['status'] === '発送済み' ? 'status-shipped' : 'status-delivered';
                                    ?>
                                    <span class="status <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($purchase['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php elseif ($show === 'settings'): ?>
                    <h2>アカウント設定</h2>
                    <p>以下の情報を編集して更新してください。</p>

                    <form method="post" action="update_profile.php">
                        <div class="form-group">
                            <h3>名前</h3>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>">
                        </div>

                        <div class="form-group">
                            <h3>メールアドレス</h3>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>

                        <div class="form-group">
                            <h3>住所</h3>
                            <textarea name="address" class="form-control"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>

                        <button type="submit" class="submit-button">更新する</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
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

    <script src="../Java
::contentReference[oaicite:24]{index=24}
 
