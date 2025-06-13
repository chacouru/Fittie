<?php
//ログイン関連func集 ＆ DB接続
require_once __DIR__ . '/../db_connect.php';

function db_connect() {
    // db_connect.php で定義された $pdo を使う
    global $pdo; 
    return $pdo;
}

// login_handler.phpに使用
// 64文字の長さのトークンを作成
function generate_token($length = 64) {
    // random_bytes()は、指定されたバイト数のランダムなバイナリデータを生成します。
    // bin2hex()は、バイナリデータを16進数の文字列に変換します。
    // 結果的に、32バイトのランダムデータ → 64文字（32個の16進数）を作成
    return bin2hex(random_bytes($length / 2));
}

// login_handler.phpに使用
function save_remember_token($user_id, $token) {
    $pdo = db_connect(); 
    $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))");

    // hash('sha256', $token)：トークンをハッシュ化してDBに保存（元のトークンは保存しない）
    // これにより、万が一データベースが漏洩しても安全性が高まる
    $stmt->execute([$user_id, hash('sha256', $token)]);
}

function get_user_by_remember_token($token) {
    $pdo = db_connect();

    // cookieから受け取ったトークンを同じようにhash('sha256')で変換して検索
    // 生のトークンではなく、ハッシュ化されたトークンと比較する
    $stmt = $pdo->prepare("SELECT user_id FROM remember_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([hash('sha256', $token)]);
    return $stmt->fetchColumn(); // user_id を取り出して返す
}

function check_login() {
    session_start();

    // すでにログイン済みなら user_id を返す
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }

    // クッキーが存在する場合は remember_me 機能でログインを復元
    if (isset($_COOKIE['remember_me'])) {
        $user_id = get_user_by_remember_token($_COOKIE['remember_me']);
        if ($user_id) {
            $_SESSION['user_id'] = $user_id;
            return $user_id;
        }
    }

    // どちらにも当てはまらなければログインページへリダイレクト
    header('Location: ./login.php');
    exit;
}
?>
