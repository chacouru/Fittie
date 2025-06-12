<?php
require_once __DIR__ . '/login_function/functions.php';
require_once 'db_connect.php';
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
// ユーザー情報とカート情報を取得
try {

  // ユーザー情報取得
  $stmt = $pdo->prepare("SELECT name, email, address, phone FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  // カート情報取得
  $stmt = $pdo->prepare("
        SELECT 
            ci.id,
            ci.product_id,
            ci.quantity,
            p.name,
            p.price,
            p.image,
            p.is_on_sale,
            p.sale_price,
            b.name as brand_name
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE ci.user_id = ?
        ORDER BY ci.id DESC
    ");
  $stmt->execute([$user_id]);
  $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // カートが空の場合はカートページにリダイレクト
  if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
  }

  // 合計金額計算
  $total = 0;
  foreach ($cart_items as $item) {
    $price = $item['is_on_sale'] ? $item['sale_price'] : $item['price'];
    $total += $price * $item['quantity'];
  }
} catch (PDOException $e) {
  die('データベースエラー: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>fitty. | 注文確認</title>
  <link rel="stylesheet" href="../CSS/reset.css">
  <link rel="stylesheet" href="../CSS/common.css">
  <link rel="stylesheet" href="../CSS/checkout.css">
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
        <div class="step current">
          <div class="step_number">2</div>
          <div class="step_label">確認</div>
        </div>
        <div class="step">
          <div class="step_number">3</div>
          <div class="step_label">完了</div>
        </div>
      </div>

      <h1 class="page_title">注文内容の確認</h1>

      <div class="checkout_content">
        <!-- 注文商品一覧 -->
        <section class="order_items_section">
          <h2 class="section_title">注文商品</h2>
          <div class="order_items">
            <?php foreach ($cart_items as $item): ?>
              <?php
              $price = $item['is_on_sale'] ? $item['sale_price'] : $item['price'];
              $subtotal = $price * $item['quantity'];
              ?>
              <div class="order_item">
                <div class="item_image">

                  <?php
                  // ブランド名がない場合は"default"を使う
                  $brand_path = $item['brand_name'] ? htmlspecialchars($item['brand_name']) : 'default';
                  $image_path = "../PHP/img/products/{$brand_path}/" . htmlspecialchars($item['image']);
                  ?>
                  <img src="<?= $image_path ?>"
                    alt="<?= htmlspecialchars($item['name']) ?>"
                    onerror="this.src='../PHP/img/products/no-image.png'">

                </div>
                <div class="item_details">
                  <div class="item_brand"><?= htmlspecialchars($item['brand_name'] ?: 'ブランド未設定') ?></div>
                  <div class="item_name"><?= htmlspecialchars($item['name']) ?></div>
                  <div class="item_size">サイズ: FREE</div>
                  <div class="item_quantity">数量: <?= $item['quantity'] ?>個</div>
                </div>
                <div class="item_price">
                  <?php if ($item['is_on_sale']): ?>
                    <span class="original_price">¥<?= number_format($item['price']) ?></span>
                    <span class="sale_price">¥<?= number_format($price) ?></span>
                  <?php else: ?>
                    <span class="price">¥<?= number_format($price) ?></span>
                  <?php endif; ?>
                </div>
                <div class="item_subtotal">¥<?= number_format($subtotal) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <!-- 配送先情報 -->
        <section class="shipping_section">
          <h2 class="section_title">配送先情報</h2>
          <div class="info_box">
            <div class="info_row">
              <span class="label">お名前:</span>
              <span class="value"><?= htmlspecialchars($user['name']) ?></span>
            </div>
            <div class="info_row">
              <span class="label">メールアドレス:</span>
              <span class="value"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="info_row">
              <span class="label">配送先住所:</span>
              <span class="value"><?= htmlspecialchars($user['address'] ?: '住所が登録されていません') ?></span>
            </div>
            <div class="info_row">
              <span class="label">電話番号:</span>
              <span class="value"><?= htmlspecialchars($user['phone'] ?: '電話番号が登録されていません') ?></span>
            </div>
          </div>
          <a href="mypage.php" class="edit_link">配送先情報を変更する</a>
        </section>

        <!-- 支払い方法 -->
        <section class="payment_section">
          <h2 class="section_title">支払い方法</h2>
          <div class="payment_methods">
            <label class="payment_option">
              <input type="radio" name="payment_method" value="credit" checked>
              <span class="option_text">クレジットカード</span>
            </label>
            <label class="payment_option">
              <input type="radio" name="payment_method" value="bank">
              <span class="option_text">銀行振込</span>
            </label>
            <label class="payment_option">
              <input type="radio" name="payment_method" value="cod">
              <span class="option_text">代金引換</span>
            </label>
          </div>
        </section>

        <!-- 注文概要 -->
        <section class="order_summary">
          <h2 class="section_title">注文概要</h2>
          <div class="summary_box">
            <div class="summary_row">
              <span class="label">商品小計:</span>
              <span class="value">¥<?= number_format($total) ?></span>
            </div>
            <div class="summary_row">
              <span class="label">送料:</span>
              <span class="value">¥0</span>
            </div>
            <div class="summary_row total_row">
              <span class="label">合計（税込）:</span>
              <span class="value">¥<?= number_format($total) ?></span>
            </div>
          </div>
        </section>

        <!-- アクションボタン -->
        <div class="action_buttons">
          <button type="button" class="back_btn" onclick="history.back()">カートに戻る</button>
          <button type="button" class="confirm_btn" onclick="confirmOrder()">注文を確定する</button>
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
  <script>
    function confirmOrder() {
      const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

      if (confirm('注文を確定しますか？\n\n※この操作は取り消せません。')) {
        // 注文確定処理へ
        window.location.href = 'order_complete.php?payment=' + paymentMethod;
      }
    }
  </script>
</body>

</html>