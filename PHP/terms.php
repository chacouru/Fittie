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
        JOIN brand b ON fb.brand_id = b.id
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
    <title>fitty. | 利用規約</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/terms.css">
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
<main>
    <h1 id="title">利用規約</h1>
    <p>株式会社fitty.（以下「当社」といいます）が運営するファッションECサイト「fitty.」（以下「本サイト」といいます）のご利用に際しての条件を、以下の通り定めます。本サイトをご利用になる前に、必ず本規約をお読みいただき、ご同意の上でご利用ください。</p>
    
    <h2>第1条（利用規約の適用）</h2>
    <p>本規約は、本サイトの利用に関わる一切の関係に適用されます。利用者は本規約に同意したものとみなされます。</p>
    
    <h2>第2条（会員登録）</h2>
    <ul>
      <li>本サイトの利用にあたり、当社が定める方法により会員登録を行うものとします。</li>
      <li>登録に際して虚偽の情報を提供した場合、当社は登録の取り消しまたは利用停止を行う権利を有します。</li>
    </ul>
    
    <h2>第3条（アカウント管理）</h2>
    <ul>
      <li>会員は自己の責任においてIDおよびパスワードを管理するものとします。</li>
      <li>第三者による不正使用が判明した場合、速やかに当社に通知しなければなりません。</li>
    </ul>
    
    <h2>第4条（禁止事項）</h2>
    <p>利用者は以下の行為を行ってはなりません。</p>
    <ul>
      <li>法令、公序良俗に違反する行為</li>
      <li>他者の権利を侵害する行為</li>
      <li>虚偽の情報提供や不正アクセス行為</li>
      <li>当社の運営を妨害する行為</li>
      <li>その他当社が不適切と判断する行為</li>
    </ul>
    
    <h2>第5条（商品の注文および支払い）</h2>
    <ul>
      <li>商品の注文は、本サイト上の手続きに従い行うものとし、当社がこれを承諾した時点で契約が成立します。</li>
      <li>支払い方法及び条件は、当社が別途定める通りとします。</li>
    </ul>
    
    <h2>第6条（商品の配送）</h2>
    <ul>
      <li>商品は登録された配送先住所に発送いたします。</li>
      <li>配送に関する遅延や破損等の問題が生じた場合、速やかに当社までご連絡ください。</li>
    </ul>
    
    <h2>第7条（返品および交換）</h2>
    <p>返品および交換については、当社が定める返品ポリシーに従うものとし、お客様のご都合による返品は原則としてお受けいたしかねますのでご了承ください。</p>
    
    <h2>第8条（知的財産権）</h2>
    <p>本サイトに掲載されているコンテンツ（文章、画像、ロゴ等）の知的財産権は当社または正当な権利者に帰属し、無断転載・複製を禁じます。</p>
    
    <h2>第9条（免責事項）</h2>
    <p>当社は、本サイトの内容について正確性および安全性の確保に努めますが、利用者が本サイトの利用により被った損害について一切の責任を負いかねます。</p>
    
    <h2>第10条（サービスの変更・停止・終了）</h2>
    <p>当社は予告なく、本サイトのサービス内容を変更、または一時的に停止、終了することがあります。</p>
    
    <h2>第11条（個人情報の取扱い）</h2>
    <p>当社は個人情報の取扱いについて、別途定めるプライバシーポリシーに従い適切に管理いたします。</p>
    
    <h2>第12条（準拠法および管轄裁判所）</h2>
    <p>本規約の解釈および適用は日本法に準拠し、本サイト利用に関する紛争は東京地方裁判所を専属的合意管轄裁判所とします。</p>
</main>
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
</body>
</html>