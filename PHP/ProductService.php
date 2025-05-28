<?php
require_once './DbManager.php';
require_once './config.php';



/**
 * 商品関連のサービスクラス
 */
class ProductService {
    private $db;
    private $cache = [];
    
    public function __construct() {
        try {
            $this->db = getDb();
        } catch (PDOException $e) {
            error_log("データベース接続エラー: " . $e->getMessage());
            throw new ProductException("データベース接続に失敗しました");
        }
    }

    /**
 * 人気商品を取得（作成日時ベース - 簡易版）
 */
public function getPopularProducts(int $limit = 10): ApiResponse {
    try {
        // 新着商品を人気商品として扱う簡易実装
        $sql = "SELECT p.id, p.name, p.price, p.image, p.category_id, p.stock, 
                       c.name as category_name, b.name as brand_name, p.created_at
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.stock > 0 AND p.is_active = 1
                ORDER BY p.created_at DESC, p.id DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return new ApiResponse(true, $products);
        
    } catch (PDOException $e) {
        error_log("人気商品の取得エラー: " . $e->getMessage());
        return new ApiResponse(false, [], "人気商品の読み込みに失敗しました");
    } catch (Exception $e) {
        error_log("予期しないエラー（人気商品）: " . $e->getMessage());        return new ApiResponse(false, [], "システムエラーが発生しました");
    }
}

    
    
    /**
     * 最近見た商品を取得（セッションベース実装）
     */
    public function getRecentlyViewed(int $userId, int $limit = RECENTLY_VIEWED_LIMIT): ApiResponse {
        try {
            // セッションから閲覧履歴を取得
            $viewHistory = $_SESSION['view_history'] ?? [];
            
            if (empty($viewHistory)) {
                return new ApiResponse(true, []);
            }
            
            // 最新の商品IDを取得（重複除去、制限適用）
            $productIds = array_unique(array_reverse($viewHistory));
            $productIds = array_slice($productIds, 0, $limit);
            
            if (empty($productIds)) {
                return new ApiResponse(true, []);
            }
            
            $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
            $sql = "SELECT p.id, p.name, p.price, p.image, p.category_id, p.stock, 
                           c.name as category_name, b.name as brand_name,
                           p.created_at
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN brands b ON p.brand_id = b.id
                    WHERE p.id IN ({$placeholders}) AND p.is_active = 1
                    ORDER BY FIELD(p.id, " . implode(',', $productIds) . ")";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($productIds);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return new ApiResponse(true, $products);
            
        } catch (PDOException $e) {
            error_log("最近見た商品の取得エラー: " . $e->getMessage());
            return new ApiResponse(false, [], "最近見た商品の読み込みに失敗しました");
        } catch (Exception $e) {
            error_log("予期しないエラー（最近見た商品）: " . $e->getMessage());
            return new ApiResponse(false, [], "システムエラーが発生しました");
        }
    }
    
    /**
     * おすすめ商品を取得（キャッシュ機能付き）
     */
    public function getRecommendedProducts(?int $userId = null, int $limit = RECOMMENDED_LIMIT): ApiResponse {
        try {
            $cacheKey = "recommended_products_{$userId}_{$limit}";
            
            // キャッシュチェック
            if (isset($this->cache[$cacheKey])) {
                return $this->cache[$cacheKey];
            }
            
            if ($userId) {
                // ユーザーの購入履歴に基づくおすすめ（JOINを使用して最適化）
                $sql = "SELECT DISTINCT p.id, p.name, p.price, p.image, p.category_id, p.stock, 
                               c.name as category_name, b.name as brand_name, p.created_at
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        LEFT JOIN brands b ON p.brand_id = b.id
                        INNER JOIN (
                            SELECT DISTINCT p2.category_id 
                            FROM products p2 
                            INNER JOIN order_items oi ON p2.id = oi.product_id 
                            INNER JOIN orders o ON oi.order_id = o.id
                            WHERE o.user_id = :user_id
                        ) uc ON p.category_id = uc.category_id
                        WHERE p.stock > 0 AND p.is_active = 1
                        ORDER BY p.created_at DESC, p.id ASC
                        LIMIT :limit";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            } else {
                // ゲストユーザー向けの商品（人気商品優先）
                $sql = "SELECT p.id, p.name, p.price, p.image, p.category_id, p.stock, 
                               c.name as category_name, b.name as brand_name, p.created_at,
                               COALESCE(order_count.total_orders, 0) as popularity
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        LEFT JOIN brands b ON p.brand_id = b.id
                        LEFT JOIN (
                            SELECT product_id, COUNT(*) as total_orders
                            FROM order_items oi
                            INNER JOIN orders o ON oi.order_id = o.id
                            WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                            GROUP BY product_id
                        ) order_count ON p.id = order_count.product_id
                        WHERE p.stock > 0 AND p.is_active = 1
                        ORDER BY popularity DESC, p.created_at DESC
                        LIMIT :limit";
                $stmt = $this->db->prepare($sql);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $result = new ApiResponse(true, $products);
            
            // キャッシュに保存（5分間）
            $this->cache[$cacheKey] = $result;
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("おすすめ商品の取得エラー: " . $e->getMessage());
            return new ApiResponse(false, [], "おすすめ商品の読み込みに失敗しました");
        } catch (Exception $e) {
            error_log("予期しないエラー（おすすめ商品）: " . $e->getMessage());
            return new ApiResponse(false, [], "システムエラーが発生しました");
        }
    }
    
    /**
     * セール商品を取得
     */
    public function getSaleProducts(int $limit = SALE_LIMIT): ApiResponse {
        try {
            // 価格の安い順で商品を取得（セール的な扱い）
            $sql = "SELECT p.id, p.name, p.price, p.image, p.category_id, p.stock, 
                           c.name as category_name, b.name as brand_name, p.created_at
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN brands b ON p.brand_id = b.id
                    WHERE p.stock > 0 AND p.is_active = 1
                    ORDER BY p.price ASC, p.created_at DESC
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return new ApiResponse(true, $products);
            
        } catch (PDOException $e) {
            error_log("セール商品の取得エラー: " . $e->getMessage());
            return new ApiResponse(false, [], "セール商品の読み込みに失敗しました");
        } catch (Exception $e) {
            error_log("予期しないエラー（セール商品）: " . $e->getMessage());
            return new ApiResponse(false, [], "システムエラーが発生しました");
        }
    }
    
    /**
     * 価格が安い商品を取得
     */
    public function getLowestPriceProducts(int $limit = LOWEST_PRICE_LIMIT): ApiResponse {
        try {
            $sql = "SELECT p.*, c.name AS category_name, b.name as brand_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN brands b ON p.brand_id = b.id
                    WHERE p.stock > 0 AND p.is_active = 1
                    ORDER BY p.price ASC, p.created_at DESC
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return new ApiResponse(true, $products);
            
        } catch (PDOException $e) {
            error_log("価格が安い商品取得エラー: " . $e->getMessage());
            return new ApiResponse(false, [], "商品の読み込みに失敗しました");
        } catch (Exception $e) {
            error_log("予期しないエラー（価格が安い商品）: " . $e->getMessage());
            return new ApiResponse(false, [], "システムエラーが発生しました");
        }
    }
    
    /**
     * ブランド情報を取得（キャッシュ機能付き）
     */
    public function getBrands(): ApiResponse {
        try {
            $cacheKey = "brands_list";
            
            // キャッシュチェック
            if (isset($this->cache[$cacheKey])) {
                return $this->cache[$cacheKey];
            }
            
            $sql = "SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $result = new ApiResponse(true, $brands);
            
            // キャッシュに保存（10分間）
            $this->cache[$cacheKey] = $result;
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("ブランド取得エラー: " . $e->getMessage());
            return new ApiResponse(false, [], "ブランド情報の読み込みに失敗しました");
        } catch (Exception $e) {
            error_log("予期しないエラー（ブランド）: " . $e->getMessage());
            return new ApiResponse(false, [], "システムエラーが発生しました");
        }
    }
    
    /**
     * 商品の閲覧履歴を記録
     */
    public function recordProductView(int $productId): void {
        try {
            // セッションに閲覧履歴を保存
            if (!isset($_SESSION['view_history'])) {
                $_SESSION['view_history'] = [];
            }
            
            // 既存の履歴から同じ商品IDを削除
            $_SESSION['view_history'] = array_filter($_SESSION['view_history'], function($id) use ($productId) {
                return $id !== $productId;
            });
            
            // 新しい商品IDを先頭に追加
            array_unshift($_SESSION['view_history'], $productId);
            
            // 履歴の最大数を制限
            $_SESSION['view_history'] = array_slice($_SESSION['view_history'], 0, MAX_VIEW_HISTORY);
            
        } catch (Exception $e) {
            error_log("閲覧履歴記録エラー: " . $e->getMessage());
            // エラーでも処理を止めない
        }
    }
    
    /**
     * キャッシュをクリア
     */
    public function clearCache(): void {
        $this->cache = [];
    }
    
    
}