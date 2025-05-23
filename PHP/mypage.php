<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/mypage.css">
    
    <title>fitty.|マイページ</title>
</head>
<body>
     <header class="header">
    <div class="header_container">
      <div class="header_menu">
        <div class="menu_button" id="menuToggle">
          <span class="bar"></span>
          <span class="bar"></span>
          <span class="bar"></span>
        </div>
        <div class="menu_overlay">
          <a href="#" class="brand_link link1">ブランドA</a>
          <a href="#" class="brand_link link2">ブランドB</a>
          <a href="#" class="brand_link link3">ブランドC</a>
          <a href="#" class="brand_link link4">ブランドD</a>
        </div>
      </div>
      <div class="header_logo">
        <h1>Fitty</h1>
      </div>
      <nav class="header_nav">
        <a href="#">マイページ</a>
        <a href="#">カート</a>
        <a href="#">検索</a>
        <a href="#">お問い合わせ</a>
      </nav>
    </div>
  </header>
  <div class="header_space"></div>

<?php
// セッション開始
session_start();

// ログインチェック（この例ではログイン済みと仮定）
// 実際の実装では、ここでログイン状態をチェックし、未ログインならログインページにリダイレクトする
$logged_in = true; // 仮のログイン状態

// ユーザー情報（実際にはデータベースから取得する）
$user = [
    'id' => 'user123',
    'name' => '山田 太郎',
    'email' => 'yamada@example.com',
    'address' => '東京都渋谷区〇〇町1-2-3'
];

// 購入履歴（実際にはデータベースから取得する）
$purchase_history = [
    // サンプルデータ
    ['id' => '10001', 'date' => '2025-05-10', 'amount' => 5800, 'status' => '発送済み'],
    ['id' => '10002', 'date' => '2025-05-05', 'amount' => 3200, 'status' => '配達完了'],
    ['id' => '10003', 'date' => '2025-04-28', 'amount' => 12000, 'status' => '配達完了']
];

// 表示する内容をリクエストパラメータから取得（デフォルトはユーザー情報）
$show = isset($_GET['show']) ? $_GET['show'] : 'profile';

// アクティブなタブのスタイルを設定するヘルパー関数
function isActive($currentTab, $tabName) {
    return $currentTab === $tabName ? 'active-tab' : '';
}
?>


<!-- マイページ本体の内容 -->
<div class="mypage-container">
    <h1 class="page-title">mypage.php</h1>
    
    <div class="profile-section">
        <div class="profile-photo"></div>
        
        <div class="profile-info">
            <div class="id-display">id: <?php echo htmlspecialchars($user['id']); ?></div>
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
                
                <h2>名前</h2>
                <p><?php echo htmlspecialchars($user['name']); ?></p>
                
                <h2>e-mail</h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                
                <h2>住所</h2>
                <p><?php echo htmlspecialchars($user['address']); ?></p>
                
            <?php elseif ($show === 'settings'): ?>
                <h2>アカウント設定</h2>
                <p>以下の情報を編集して更新してください。</p>
                
                <form method="post" action="update_profile.php">
                    <div class="form-group">
                        <h2>名前</h2>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <h2>e-mail</h2>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <h2>住所</h2>
                        <textarea name="address" class="form-control"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <button type="submit" class="submit-button">更新する</button>
                </form>
                
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
            <?php endif; ?>
        </div>
    </div>
</div>

<<<<<<< HEAD
 <footer class="footer">
=======
<!-- footer -->
  <footer class="footer">
>>>>>>> a826b6f497139443ab5d5d21dbc7b3c0b2c1f8c5
    <div class="footer_container">
      <div class="footer_logo">
        <h2>Fitty</h2>
      </div>
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
        <small>&copy; 2025 fitty. All rights reserved.</small>
      </div>
    </div>
  </footer>
<<<<<<< HEAD
    
=======
  <!-- footer -->
     <script src="../JavaScript/hamburger.js"></script>
>>>>>>> a826b6f497139443ab5d5d21dbc7b3c0b2c1f8c5
</body>

<script src="../JavaScript/hamburger.js"></script>

</html>
