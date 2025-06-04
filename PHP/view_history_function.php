<?php
/**
 * 閲覧履歴関連の関数
 */

/**
 * ユーザーの閲覧履歴を取得
 * @param PDO $pdo データベース接続
 * @param int $user_id ユーザーID
 * @param int $limit 取得件数（デフォルト: 5）
 * @return array 閲覧履歴の配列
 */
function getUserViewHistory($pdo, $user_id, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT vh.*, p.name, p.price, p.image, p.is_on_sale, p.sale_price, b.name as brand_name
        FROM view_history vh
        JOIN products p ON vh.product_id = p.id
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE vh.user_id = ? AND p.is_active = 1
        ORDER BY vh.viewed_at DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 閲覧履歴を追加（最新5件を保持）
 * @param PDO $pdo データベース接続
 * @param int $user_id ユーザーID
 * @param int $product_id 商品ID
 * @return bool 成功した場合true
 */
function addViewHistory($pdo, $user_id, $product_id) {
    try {
        // トランザクション開始
        $pdo->beginTransaction();
        
        // 既存の同じ商品の履歴をチェック
        $stmt = $pdo->prepare("SELECT id FROM view_history WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // 既存の履歴がある場合は閲覧日時を更新
            $stmt = $pdo->prepare("UPDATE view_history SET viewed_at = NOW() WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
        } else {
            // 履歴件数をチェック
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM view_history WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $count = $stmt->fetchColumn();
            
            if ($count >= 5) {
                // 5件以上ある場合、最も古い履歴を削除
                $stmt = $pdo->prepare("
                    DELETE FROM view_history 
                    WHERE user_id = ? 
                    ORDER BY viewed_at ASC 
                    LIMIT 1
                ");
                $stmt->execute([$user_id]);
            }
            
            // 新しい履歴を追加
            $stmt = $pdo->prepare("INSERT INTO view_history (user_id, product_id, viewed_at) VALUES (?, ?, NOW())");
            $stmt->execute([$user_id, $product_id]);
        }
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollback();
        return false;
    }
}

/**
 * 特定ユーザーの古い閲覧履歴をクリーンアップ
 * @param PDO $pdo データベース接続
 * @param int $user_id ユーザーID
 * @param int $keep_count 保持する件数（デフォルト: 5）
 * @return int 削除された件数
 */
function cleanupViewHistory($pdo, $user_id, $keep_count = 5) {
    $stmt = $pdo->prepare("
        DELETE FROM view_history 
        WHERE user_id = ? 
        AND id NOT IN (
            SELECT * FROM (
                SELECT id 
                FROM view_history 
                WHERE user_id = ? 
                ORDER BY viewed_at DESC 
                LIMIT ?
            ) AS keep_records
        )
    ");
    $stmt->execute([$user_id, $user_id, $keep_count]);
    return $stmt->rowCount();
}

/**
 * 全ユーザーの古い閲覧履歴をクリーンアップ（バッチ処理用）
 * @param PDO $pdo データベース接続
 * @param int $keep_count 保持する件数（デフォルト: 5）
 * @return int 削除された件数
 */
function cleanupAllViewHistory($pdo, $keep_count = 5) {
    $total_deleted = 0;
    
    // 全ユーザーを取得
    $stmt = $pdo->query("SELECT DISTINCT user_id FROM view_history");
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($users as $user_id) {
        $deleted = cleanupViewHistory($pdo, $user_id, $keep_count);
        $total_deleted += $deleted;
    }
    
    return $total_deleted;
}

/**
 * 閲覧履歴をHTML形式でフォーマット
 * @param array $history 閲覧履歴の配列
 * @return string HTML文字列
 */
function formatViewHistoryHTML($history) {
    if (empty($history)) {
        return '<p>閲覧履歴はありません。</p>';
    }
    
    $html = '<div class="view-history-grid">';
    
    foreach ($history as $item) {
        $price = $item['is_on_sale'] && $item['sale_price'] 
                ? number_format($item['sale_price']) 
                : number_format($item['price']);
        
        $brand = $item['brand_name'] ? htmlspecialchars($item['brand_name']) : '';
        
        $html .= '
        <div class="history-item">
            <a href="product_detail.php?id=' . $item['product_id'] . '">
                <img src="./img/products/' . htmlspecialchars($item['image']) . '" 
                     alt="' . htmlspecialchars($item['name']) . '">
                <h3>' . htmlspecialchars($item['name']) . '</h3>
                ' . ($brand ? '<p class="brand">' . $brand . '</p>' : '') . '
                <p class="price">¥' . $price . '</p>
                <p class="viewed-date">' . date('m/d H:i', strtotime($item['viewed_at'])) . '</p>
            </a>
        </div>';
    }
    
    $html .= '</div>';
    
    return $html;
}
?>