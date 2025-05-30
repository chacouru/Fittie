<?php
require_once './login_function/functions.php';
require_once 'db_connect.php';

$user_id = check_login(); // ログインユーザーの確認

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && $user_id == $_POST['id']) {
    // 入力データの取得・サニタイズ
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $brands = $_POST['brands'] ?? []; // チェックされたブランドIDの配列

    // トランザクション開始
    $pdo->beginTransaction();

    try {
        // ユーザー情報の更新
        $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, address = :address WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':address' => $address,
            ':id' => $user_id
        ]);

        // お気に入りブランドを更新するために、まず既存データを削除
        $stmt = $pdo->prepare("DELETE FROM favorite_brands WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);

        // 新しいお気に入りブランドを挿入
        if (!empty($brands)) {
            $stmt = $pdo->prepare("INSERT INTO favorite_brands (user_id, brand_id) VALUES (:user_id, :brand_id)");
            foreach ($brands as $brand_id) {
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':brand_id' => $brand_id
                ]);
            }
        }

        // コミット
        $pdo->commit();
        header('Location: mypage.php?show=settings&success=1');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "更新中にエラーが発生しました: " . htmlspecialchars($e->getMessage());
        exit;
    }

} else {
    echo '不正なアクセスです。';
    exit;
}
?>
