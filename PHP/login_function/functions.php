<?php
function db_connect() {
    return new PDO('mysql:host=localhost;dbname=fitty', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}

function generate_token($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

function save_remember_token($user_id, $token) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))");
    $stmt->execute([$user_id, hash('sha256', $token)]);
}

function get_user_by_remember_token($token) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT user_id FROM remember_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([hash('sha256', $token)]);
    return $stmt->fetchColumn();
}

function check_login() {
    session_start();

    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }

    if (isset($_COOKIE['remember_me'])) {
        $user_id = get_user_by_remember_token($_COOKIE['remember_me']);
        if ($user_id) {
            $_SESSION['user_id'] = $user_id;
            return $user_id;
        }
    }

    header('Location: ./login.php');
    exit;
}?>
<?php
// functions.php
// session_start();

// function check_login() {
//     if (!isset($_SESSION['user_id'])) {
//         header('Location: login.php');
//         exit;
//     }
//     return $_SESSION['user_id'];
// }
// ?>

