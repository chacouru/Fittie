<?php
/**
 * 商品閲覧追跡API
 */

session_start();
require_once '../DbManager.php';
require_once '../Encode.php';
require_once '../ProductService.php';

// CORS対応とContent-Typeの設定
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// POSTリクエストのみ受け付け
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
}

// AJAX リクエストかチェック
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request'
    ]);
    exit;
}

try {
    // JSONデータを取得
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('Invalid JSON data');
    }
    
    // 必須パラメータのチェック
    if (!isset($data['product_id']) || !is_numeric($data['product_id'])) {
        throw new InvalidArgumentException('Product ID is required and must be numeric');
    }
    
    $productId = (int)$data['product_id'];
    $context = isset($data['context']) ? trim($data['context']) : 'unknown';
    $timestamp = isset($data['timestamp']) ? (int)$data['timestamp'] : time();
    
    // 商品IDの妥当性チェック
    if ($productId <= 0) {
        throw new InvalidArgumentException('Invalid product ID');
    }
    
    // ProductServiceを使用して閲覧履歴を記録
    $productService = new ProductService();
    $productService->recordProductView($productId);
    
    // 成功レスポンス
    echo json_encode([
        'success' => true,
        'message' => 'View tracked successfully',
        'data' => [
            'product_id' => $productId,
            'context' => $context,
            'recorded_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("閲覧追跡APIエラー（不正なパラメータ）: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid parameters'
    ]);
    
} catch (ProductException $e) {
    error_log("閲覧追跡APIエラー（商品関連）: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to track view'
    ]);
    
} catch (Exception $e) {
    error_log("閲覧追跡APIエラー（予期しないエラー）: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}
?>