<?php
require_once __DIR__ . '/../DbManager.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];

    try {
        $pdo = getDb();

        // 削除処理
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // エラー内容を JSON で返す（開発用）
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
