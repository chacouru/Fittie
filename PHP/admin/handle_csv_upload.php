<?php
require_once '../db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($file, 'r')) !== false) {
        // PDOオブジェクトを直接使用
        // getDb()関数は使用しない
        $header = fgetcsv($handle); // ヘッダー読み飛ばし
        $count = 0;
        try {
            // トランザクション開始
            $pdo->beginTransaction();
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 7) continue;
                // カラム順: 商品名,説明,価格,カテゴリID,在庫,ブランドID,画像ファイル名
                [$name, $description, $price, $category_id, $stock, $brand_id, $image] = array_pad($row, 7, null);
                // 型変換
                $price = (int)$price;
                $category_id = (int)$category_id;
                $stock = (int)$stock;
                $brand_id = (int)$brand_id;
                $stmt = $pdo->prepare("INSERT INTO products
                    (name, description, price, category_id, stock, brand_id, image)
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $category_id, $stock, $brand_id, $image]);
                $count++;
            }
            // トランザクションをコミット
            $pdo->commit();
            fclose($handle);
            
            // 成功時：セッションにメッセージを保存してリダイレクト
            session_start();
            $_SESSION['success_message'] = "{$count} 件の商品をCSVから登録しました。";
            header('Location: add_product.php');
            exit;
            
        } catch (PDOException $e) {
            // エラーが発生した場合はロールバック
            $pdo->rollBack();
            fclose($handle);
            error_log('データベースエラー: ' . $e->getMessage());
            
            // エラー時：セッションにエラーメッセージを保存してリダイレクト
            session_start();
            $_SESSION['error_message'] = "データベースエラーが発生しました。";
            header('Location: add_product.php');
            exit;
        }
    } else {
        // ファイル読み込み失敗時
        session_start();
        $_SESSION['error_message'] = "ファイルの読み込みに失敗しました。";
        header('Location: add_product.php');
        exit;
    }
} else {
    // CSVファイル未選択時
    session_start();
    $_SESSION['error_message'] = "CSVファイルを選択してください。";
    header('Location: add_product.php');
    exit;
}
?>