<?php
session_start();
require_once 'db_connect.php'; // DB接続ファイルを読み込む

$brands = [];
$user_id = null;

// ログインしている場合、お気に入りブランドを取得
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
}?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>fitty. | Q&A</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
  <link rel="stylesheet" href="../CSS/qa.css">
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
  <a href="logout.php">
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

<div class="header_space"></div>
<!-- headerここまで -->
<div class="backdrop" id="menuBackdrop"></div>

<?php if (isset($_SESSION['user_id'])): ?>
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
  <!-- headerここまで -->
<main>

    <h1>Q&A</h1>

    <p>Q1. サイズ選びに迷っています。どのように選べばよいですか？</p>
    <p>A1. 各商品ページにサイズガイドを掲載しております。また、商品によってはスタッフの着用コメントもございますので、そちらも参考にしてください。</p>
    <p>Q2. 商品の色味が写真と異なることはありますか？</p>
    <p>A2. できるだけ実物に近い色味で掲載しておりますが、ご利用のモニターや照明環境により、実際の色と異なる場合がございます。予めご了承ください。</p>
    <p>Q3. 注文から発送までどのくらいかかりますか？</p>
    <p>A3. 通常、ご注文から2～3営業日以内に発送いたします。ただし、セール期間中やご注文が集中した場合は、発送までにお時間をいただくことがございます。</p>
    <p>Q4. 配送日時の指定はできますか？</p>
    <p>A4. はい、ご注文時に配送日時の指定が可能です。ただし、一部地域や商品によってはご希望に添えない場合がございます。</p>
    <p>Q5. 利用できる支払い方法を教えてください。</p>
    <p>A5. クレジットカード（VISA、MasterCard、JCB）、コンビニ決済、代金引換、銀行振込などをご利用いただけます。詳細は「お支払い方法」ページをご確認ください。</p>
    <p>Q6. 領収書の発行は可能ですか？</p>
    <p>A6. はい、領収書の発行が可能です。ご注文時に「領収書希望」とご記入いただくか、お問い合わせフォームよりご連絡ください。</p>
    <p>Q7. 商品の返品や交換はできますか？</p>
    <p>A7. 商品到着後7日以内であれば、未使用・未開封の商品に限り返品・交換を承っております。詳細は「返品・交換について」ページをご確認ください。</p>
    <p>Q8. 不良品が届いた場合、どうすればよいですか？</p>
    <p>A8. 大変申し訳ございません。商品到着後7日以内にお問い合わせフォームよりご連絡ください。速やかに対応させていただきます。</p>
    <p>Q9. パスワードを忘れてしまいました。どうすればよいですか？</p>
    <p>A9. ログインページの「パスワードをお忘れの方」リンクから再設定手続きを行ってください。</p>
    <p>Q10. メールマガジンの配信を停止したいのですが。</p>
    <p>A10. マイページの「会員情報の変更」からメールマガジンの配信設定を変更いただけます。</p>

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