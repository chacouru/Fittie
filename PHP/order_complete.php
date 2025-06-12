<?php
require_once __DIR__ . '/login_function/functions.php';
require_once __DIR__ . '/db_connect.php'; // ← 追加

$user_id = check_login(); // 未ログインの場合は login.php にリダイレクト

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

// 注文処理
try {
    $pdo->beginTransaction(); // ← db_connect.phpの$pdoを使う

    // カート内容を取得
    $stmt = $pdo->prepare("
        SELECT 
            ci.product_id,
            ci.quantity,
            p.price,
            p.is_on_sale,
            p.sale_price
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($cart_items)) {
        $total = 0;
        foreach ($cart_items as $item) {
            $price = $item['is_on_sale'] ? $item['sale_price'] : $item['price'];
            $total += $price * $item['quantity'];
        }

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");
        $stmt->execute([$user_id, $total]);
        $order_id = $pdo->lastInsertId();

        foreach ($cart_items as $item) {
            $price = $item['is_on_sale'] ? $item['sale_price'] : $item['price'];
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $price]);
        }

        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$user_id]);

        $pdo->commit();
        $order_success = true;
        $order_number = str_pad($order_id, 8, '0', STR_PAD_LEFT);
    } else {
        $order_success = false;
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    $order_success = false;
    error_log('Order processing error: ' . $e->getMessage());
}


// 注文が失敗した場合はカートページにリダイレクト
if (!$order_success) {
    header('Location: cart.php?error=order_failed');
    exit;
}

$payment_method = $_GET['payment'] ?? 'credit';
$payment_names = [
    'credit' => 'クレジットカード',
    'bank' => '銀行振込',
    'cod' => '代金引換'
];
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>fitty. | 注文完了</title>
  <link rel="stylesheet" href="../CSS/reset.css">
  <link rel="stylesheet" href="../CSS/common.css">
  <link rel="stylesheet" href="../CSS/order_complete.css">
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
  <a href="login.php">
    <img src="./img/login.png" alt="ログイン">
  </a>
</div>
';
    }?>
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

<div class="backdrop" id="menuBackdrop"></div>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="menu_overlay" id="globalMenu" role="navigation" aria-hidden="true">
  <nav>
    <?php if (!empty($brands)): ?>
      <?php foreach ($brands as $index => $brand): ?>
        <a href="brand.php?id=<?= htmlspecialchars($brand['id']) ?>"
   role="menuitem"
   class="brand">
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
  <!-- headerここまで -->

  <main>
    <div class="container">
      <!-- 進行状況 -->
      <div class="progress_bar">
        <div class="step completed">
          <div class="step_number">1</div>
          <div class="step_label">カート</div>
        </div>
        <div class="step completed">
          <div class="step_number">2</div>
          <div class="step_label">確認</div>
        </div>
        <div class="step current">
          <div class="step_number">3</div>
          <div class="step_label">完了</div>
        </div>
      </div>

      <!-- 完了メッセージ -->
      <div class="completion_message">
        <div class="success_icon">✓</div>
        <h1 class="completion_title">ご注文ありがとうございました！</h1>
        <p class="completion_text">
          ご注文を正常に受け付けました。<br>
          注文確認メールを送信いたしましたので、ご確認ください。
        </p>
      </div>

      <!-- 注文情報 -->
      <div class="order_info">
        <div class="info_section">
          <h2 class="section_title">注文情報</h2>
          <div class="info_box">
            <div class="info_row">
              <span class="label">注文番号:</span>
              <span class="value order_number">#<?= $order_number ?></span>
            </div>
            <div class="info_row">
              <span class="label">注文日時:</span>
              <span class="value"><?= date('Y年m月d日 H:i') ?></span>
            </div>
            <div class="info_row">
              <span class="label">支払い方法:</span>
              <span class="value"><?= $payment_names[$payment_method] ?? $payment_method ?></span>
            </div>
            <div class="info_row">
              <span class="label">合計金額:</span>
              <span class="value total_amount">¥<?= number_format($total) ?></span>
            </div>
          </div>
        </div>

        <!-- 次のステップ -->
        <div class="next_steps">
          <h2 class="section_title">今後の流れ</h2>
          <div class="steps_list">
            <div class="step_item">
              <div class="step_icon">📧</div>
              <div class="step_content">
                <h3>注文確認メール送信</h3>
                <p>ご登録のメールアドレスに注文確認メールをお送りします。</p>
              </div>
            </div>
            <div class="step_item">
              <div class="step_icon">📦</div>
              <div class="step_content">
                <h3>商品の準備・発送</h3>
                <p>2-3営業日以内に商品を発送いたします。</p>
              </div>
            </div>
            <div class="step_item">
              <div class="step_icon">🚚</div>
              <div class="step_content">
                <h3>配送完了</h3>
                <p>発送時に配送状況をメールでお知らせします。</p>
              </div>
            </div>
          </div>
        </div>

        <!-- 支払い方法別の案内 -->
        <?php if ($payment_method === 'bank'): ?>
        <div class="payment_notice">
          <h2 class="section_title">お支払いについて</h2>
          <div class="notice_box bank_transfer">
            <h3>銀行振込でのお支払い</h3>
            <p>以下の口座にお振込みください。振込手数料はお客様負担となります。</p>
            <div class="bank_info">
              <p><strong>【振込先】</strong></p>
              <p>銀行名：○○銀行 ○○支店</p>
              <p>口座種別：普通預金</p>
              <p>口座番号：1234567</p>
              <p>口座名義：株式会社フィッティー</p>
            </div>
            <p class="important">※入金確認後、商品を発送いたします。</p>
          </div>
        </div>
        <?php elseif ($payment_method === 'cod'): ?>
        <div class="payment_notice">
          <h2 class="section_title">お支払いについて</h2>
          <div class="notice_box cod">
            <h3>代金引換でのお支払い</h3>
            <p>商品お届け時に配送業者へ代金をお支払いください。</p>
            <p class="important">※代引き手数料330円が別途かかります。</p>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- アクションボタン -->
      <div class="action_buttons">
        <a href="mypage.php" class="order_history_btn">注文履歴を見る</a>
        <a href="index.php" class="continue_shopping_btn">ショッピングを続ける</a>
      </div>

      <!-- お問い合わせ -->
      <div class="contact_section">
        <h2 class="section_title">ご不明な点がございましたら</h2>
        <p>ご注文に関するお問い合わせは、注文番号を明記の上、下記までご連絡ください。</p>
        <div class="contact_info">
          <p>📞 カスタマーサポート: 0120-123-456</p>
          <p>📧 メール: <a href="mailto:support@fitty.com">support@fitty.com</a></p>
          <p>🕒 受付時間: 平日 9:00-18:00</p>
        </div>
      </div>
    </div>
  </main>

  <!-- フッターここから -->
 <footer class="footer">
    <div class="footer_container">
        <a href="index.php"><div class="footer_logo"><h2>fitty.</h2></div></a>
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

<script src="../JavaScript/hamburger.js"></script>
</body>
</html>