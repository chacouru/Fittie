<?php
require_once 'db_connect.php';
require_once './login_function/functions.php';

$user_id = $_POST['id'] ?? null;
if (!$user_id) {
    die('ユーザーIDが指定されていません。');
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$brands = $_POST['brands'] ?? [];

// ユーザー情報更新
$stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, address = :address WHERE id = :id");
$stmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':address' => $address,
    ':id' => $user_id
]);

// お気に入りブランドの更新（トランザクション推奨）
$pdo->beginTransaction();

// 既存のユーザーのfavorite_brandsを削除
$stmt = $pdo->prepare("DELETE FROM favorite_brands WHERE user_id = :id");
$stmt->execute([':id' => $user_id]);

// 新しいブランドを挿入
$stmt = $pdo->prepare("INSERT INTO favorite_brands (user_id, brand_id) VALUES (:user_id, :brand_id)");
foreach ($brands as $brand_id) {
    $stmt->execute([
        ':user_id' => $user_id,
        ':brand_id' => $brand_id
    ]);
}

$pdo->commit();

// 更新後にマイページへ戻る
header('Location: mypage.php?show=settings');
exit;
