<?php
/**
 * 目的：ログインユーザーのカート情報をDBから取得してJSONで返すAPI。
 * フロントエンドのJS（cart.js）がこのPHPを fetch() して、JSONを受け取って表示する。 
 */
require_once __DIR__ . '/login_function/functions.php';
require_once __DIR__ . '/DbManager.php';

try {
    $user_id = check_login(); // ログイン確認してユーザーID取得
    $pdo = getDb(); // PDOインスタンスを取得

    $sql = "SELECT
              ci.id,
              ci.product_id,
              p.name,
              p.price,
              p.image,
              p.stock,
              ci.quantity,
              b.name AS brand_name
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE ci.user_id = :user_id
            ORDER BY ci.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 画像パスを生成する関数
    function getImagePath($imageName, $brandName = null) {
        $basePath = 'img/products/';
        
        if (empty($imageName)) {
            return $basePath . 'no-image.png';
        }
        
        $possiblePaths = [];
        
        if ($brandName) {
            $brandFolder = strtolower(str_replace([' ', '°'], ['_', ''], $brandName));
            $possiblePaths[] = $basePath . $brandFolder . '/' . $imageName;
        }
        
        $possiblePaths[] = $basePath . 'default/' . $imageName;
        $possiblePaths[] = $basePath . $imageName;
        
        foreach ($possiblePaths as $path) {
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $path)) {
                return $path;
            }
        }
        
        return $basePath . 'no-image.png';
    }

    // 各アイテムに正しい画像パスを設定
    foreach ($items as &$item) {
        $item['image_path'] = getImagePath($item['image'], $item['brand_name']);
        $item['subtotal'] = $item['price'] * $item['quantity'];
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'items' => $items
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'ログインが必要です'
    ], JSON_UNESCAPED_UNICODE);
}
?>