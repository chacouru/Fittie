<?php
require_once __DIR__ . '/../db_connect.php';

// POSTリクエストであることを確認
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '不正なリクエストメソッドです']);
    exit;
}

// ユーザーIDの検証
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => '無効なユーザーIDです']);
    exit;
}

try {
    // トランザクション開始
    $pdo->beginTransaction();

    // 注文履歴の削除（外部キー制約がある場合）
    $sql_delete_orders = "DELETE FROM orders WHERE user_id = :user_id";
    $stmt_orders = $pdo->prepare($sql_delete_orders);
    $stmt_orders->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_orders->execute();

    // ユーザーの削除
    $sql_delete_user = "DELETE FROM users WHERE id = :user_id";
    $stmt_user = $pdo->prepare($sql_delete_user);
    $stmt_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_user->execute();

    // トランザクションをコミット
    $pdo->commit();

    // 成功レスポンス
    echo json_encode(['success' => true, 'message' => 'ユーザーを削除しました']);
    exit;

} catch (PDOException $e) {
    // エラーが発生した場合はロールバック
    $pdo->rollBack();

    // エラーログに記録
    error_log('ユーザー削除エラー: ' . $e->getMessage());

    // エラーレスポンス
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'ユーザーの削除中にエラーが発生しました',
        'error_details' => $e->getMessage()
    ]);
    exit;
}