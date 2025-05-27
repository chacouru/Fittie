<?php
/**
 * 商品取得API
 */

session_start();
require_once '../DbManager.php';
require_once '../Encode.php';
require_once '../ProductService.php';
require_once '../config.php';

// CORS対応とContent-Typeの設定
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// GETリクエストのみ受け付け
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
    // パラメータの取得と検証
    $section = isset($_GET['section']) ? trim($_GET['section']) : '';
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 10;
    $userId = $_SESSION['user_id'] ?? null;
    
    // セクションの妥当性チェック
    $allowedSections = ['recommended', 'sale', 'recent', 'cheap', 'popular'];
    if (!in_array($section, $allowedSections)) {
        throw new InvalidArgumentException('Invalid section parameter');
    }
    
    $productService = new ProductService();
    $response = null;
    
    // セクションに応じて商品を取得
    switch ($section) {
        case 'recommended':
            $response = $productService->getRecommendedProducts($userId, $limit);
            break;
            
        case 'sale':
            $response = $productService->getSaleProducts($limit);
            break;
            
        case 'recent':
            if ($userId) {
                $response = $productService->getRecentlyViewed($userId, $limit);
            } else {
                $response = new ApiResponse(true, []);
            }
            break;
            
        case 'cheap':
            $response = $productService->getLowestPriceProducts($limit);
            break;
            
        case 'popular':
            $response = $productService->getPopularProducts($limit);
            break;
            
        default:
            throw new InvalidArgumentException('Unsupported section');
    }
    
    if (!$response || !$response->success) {
        throw new ProductException($response->error ?? 'Failed to fetch products');
    }
    
    // 商品データを整形
    $formattedProducts = array_map(function($product) {
        return [
            'id' => (int)$product['id'],
            'name' => $product['name'],
            'price' => (int)$product['price'],
            'image_path' => getImagePath($product['image'] ?? '', $product['brand_name'] ?? ''),
            'brand_name' => $product['brand_name'] ?? '',
            'category_name' => $product['category_name'] ?? '',
            'stock' => (int)($product['stock'] ?? 0),
            'created_at' => $product['created_at'] ?? null
        ];
    }, $response->data);
    
    // 成功レスポンス
    echo json_encode([
        'success' => true,
        'products' => $formattedProducts,
        'pagination' => [
            'offset' => $offset,
            'limit' => $limit,
            'count' => count($formattedProducts),
            'has_more' => count($formattedProducts) === $limit
        ],
        'metadata' => [
            'section' => $section,
            'user_id' => $userId,
            'fetched_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("商品取得APIエラー（不正なパラメータ）: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid parameters',
        'details' => $e->getMessage()
    ]);
    
} catch (ProductException $e) {
    error_log("商品取得APIエラー（商品関連）: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch products',
        'details' => $e->getMessage()
    ]);
    
} catch (Exception $e) {
    error_log("商品取得APIエラー（予期しないエラー）: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}

/**
 * 画像パスを処理する関数
 */
function getImagePath(string $imagePath, string $brandName = ''): string {
    if (empty($imagePath)) {
        return DEFAULT_NO_IMAGE;
    }

    // 絶対パスやURLの場合はそのまま返す
    if (str_starts_with($imagePath, 'http') || 
        str_starts_with($imagePath, '/') || 
        str_starts_with($imagePath, '../')) {
        return $imagePath;
    }

    // ブランド名が指定されている場合はブランドディレクトリを含める
    if (!empty($brandName)) {
        $safeBrandName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $brandName);
        return PRODUCT_IMAGE_PATH . $safeBrandName . '/' . $imagePath;
    }

    return PRODUCT_IMAGE_PATH . $imagePath;
}
?>