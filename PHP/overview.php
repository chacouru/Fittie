<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>fitty. | 会社概要</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/overview.css">
</head>
<body>
     <!-- headerここから -->
  <header class="header">
    <button class="menu_button" id="menuToggle" aria-label="メニューを開閉" aria-expanded="false" aria-controls="globalMenu"> <span class="bar"></span><span class="bar"></span><span class="bar"></span> </button>
    <div class="header_logo">
      <h1><a href="./toppage.php">fitty.</a></h1>
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
<main>
  <h1 id="title">会社概要</h1>
  <p>会社名：株式会社fitty.（フィッティー）</p>
  <p>設立：2025年5月23日</p>
  <p>所在地：東京都〇〇区〇〇1-1-11</p>
  <p>代表者：代表取締役 Nguyễn Thị Minh Anh-Lê <br>(グエン・ティ・ミン・アイン＝レー)</p>
  <img src="./img/ceo.jpeg" alt="ceo">
  <p>資本金：1,000万円</p>

  <section class="ceo-profile">
  <h2>代表取締役 経歴</h2>
  <ul>
    <li>1986年10月：ベトナム・ハノイ市に生まれる</li>
    <li>2004年：ハノイ外国語大学 英語学部 入学</li>
    <li>2008年：同大学 卒業（学士号取得）</li>
    <li>2010年：日本・東京のファッション専門学校 入学（ファッションビジネス専攻）</li>
    <li>2012年：同校 卒業</li>
    <li>2012年〜2015年：東京のアパレル企業でマーケティング担当として勤務</li>
    <li>2015年〜2018年：ベトナム国内ファッションブランドのEC部門立ち上げに従事</li>
    <li>2018年〜2020年：国内外のファッションECプロジェクトにコンサルタントとして参画</li>
    <li>2021年：ファッションECサイト「fitty.」を設立</li>
    <li>2025年：株式会社fitty.として法人化、代表取締役に就任</li>
  </ul>
</section>

  <section class="ceo-message">
    <h2>代表挨拶</h2>
    <p>
      「fitty.」は、“自分らしさを纏う喜び”をすべての人に届けることをミッションに掲げて誕生しました。グローバルな視点と、アジア的な美意識を融合させ、ただモノを売るだけではなく、共感やストーリーを届けるブランドとして成長していきます。<br><br>
      お客様、パートナーの皆様、そして社会全体とともに、サステナブルで多様性に富んだファッション文化を育んでまいります。どうぞご期待ください。
    </p>
    <p class="ceo-signature">代表取締役 Nguyễn Thị Minh Anh-Lê</p>
  </section>
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