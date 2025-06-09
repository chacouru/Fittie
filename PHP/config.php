<?php
/**
 * アプリケーション設定ファイル
 */

// 商品表示関連の設定
define('RECOMMENDED_LIMIT', 10);           // おすすめ商品の表示数
define('SALE_LIMIT', 10);                 // セール商品の表示数
define('RECENTLY_VIEWED_LIMIT', 11);      // 最近見た商品の表示数
define('LOWEST_PRICE_LIMIT', 5);          // 価格が安い商品の表示数
define('MAX_VIEW_HISTORY', 50);           // 閲覧履歴の最大保存数

// キャッシュ関連の設定
define('CACHE_DURATION_SHORT', 300);      // 5分（秒）
define('CACHE_DURATION_MEDIUM', 600);     // 10分（秒）
define('CACHE_DURATION_LONG', 3600);      // 1時間（秒）

// エラーメッセージ
define('ERROR_DB_CONNECTION', 'データベース接続に失敗しました。しばらく時間をおいてから再度お試しください。');
define('ERROR_PRODUCT_LOAD', '商品情報の読み込みに失敗しました。');
define('ERROR_SYSTEM', 'システムエラーが発生しました。管理者にお問い合わせください。');

// 画像関連の設定
define('DEFAULT_NO_IMAGE', '../PHP/img/no-image.jpg');
define('PRODUCT_IMAGE_PATH', '../PHP/img/products/');

// ページング関連
define('PRODUCTS_PER_PAGE', 20);          // 商品一覧のページあたり表示数

// セキュリティ関連
define('MAX_LOGIN_ATTEMPTS', 5);          // ログイン試行回数制限
define('LOCKOUT_TIME', 900);              // ロックアウト時間（15分）

// メール関連（必要に応じて設定）
define('ADMIN_EMAIL', 'admin@fitty.com');
define('SYSTEM_EMAIL', 'system@fitty.com');