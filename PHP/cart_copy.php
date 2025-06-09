<?php
/**
 * カートページ
 * APIからカート情報を取得してフロントエンドで表示
 */
require_once __DIR__ . '/login_function/functions.php';

// ログイン確認（ログインしていない場合はlogin.phpにリダイレクト）
try {
    $user_id = check_login();
} catch (Exception $e) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ショッピングカート - fitty.</title>
  <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/cart.css"></head>
<body>
    <!-- ヘッダー -->
    <header class="header">
        <div class="header-container">
            <div class="header-left">
                <button class="menu-btn">☰</button>
            </div>
            <div class="header-center">
                <h1 class="logo">fitty.</h1>
            </div>
            <div class="header-right">
                <button class="icon-btn">👤</button>
                <button class="icon-btn">🛒</button>
                <button class="icon-btn">🔍</button>
                <button class="icon-btn">💬</button>
            </div>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="main-content">
        <div class="cart-header">
            <h2 id="cart-title">カートに入っている商品：読み込み中...</h2>
        </div>

        <div class="cart-container">
            <div id="loading" class="loading">
                <p>カート情報を読み込んでいます...</p>
            </div>
            
            <div id="empty-cart" class="empty-cart" style="display: none;">
                <p>カートに商品はありません。</p>
                <a href="index.php" class="continue-shopping">ショッピングを続ける</a>
            </div>

            <div id="cart-items" class="cart-items" style="display: none;">
                <!-- カート商品がここに動的に挿入される -->
            </div>

            <div id="cart-summary" class="cart-summary" style="display: none;">
                <div class="total-section">
                    <div class="total-row">
                        <span class="total-label">合計（税込）</span>
                        <span class="total-amount" id="total-amount">¥0</span>
                    </div>
                </div>
                
                <button class="checkout-btn" id="checkout-btn">
                    レジへ進む
                </button>
            </div>
        </div>
    </main>

    <!-- フッター -->
    <footer class="footer">
        <div class="footer-logo">fitty.</div>
        <div class="footer-links">
            <a href="#">会社概要</a>
            <a href="#">利用規約</a>
            <a href="#">プライバシーポリシー</a>
        </div>
        <div class="footer-social">
            <a href="#" class="social-link">📘</a>
            <a href="#" class="social-link">📷</a>
            <a href="#" class="social-link">🐦</a>
        </div>
    </footer>

    <script src="JavaScript/cart.js"></script>
</body>
</html>