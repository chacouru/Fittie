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
            echo "{$count} 件の商品をCSVから登録しました。";

        } catch (PDOException $e) {
            // エラーが発生した場合はロールバック
            $pdo->rollBack();
            error_log('データベースエラー: ' . $e->getMessage());
            echo "データベースエラーが発生しました。";
        }
    } else {
        echo "ファイルの読み込みに失敗しました。";
    }
} else {
    echo "CSVファイルを選択してください。";
}
?>