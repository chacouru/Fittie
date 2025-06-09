<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, 'r')) !== false) {
        $pdo = new PDO('mysql:host=localhost;dbname=fitty;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $header = fgetcsv($handle); // ヘッダー読み飛ばし
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 7) continue;
            
            // カラム順: 商品名,説明,価格,カテゴリID,在庫,ブランドID,画像ファイル名
            [$name, $description, $price, $category_id, $stock, $brand_id, $image] = array_pad($row, 7, null);

            // 必要に応じて型変換
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

        fclose($handle);
        echo "{$count} 件の商品をCSVから登録しました。";
    } else {
        echo "ファイルの読み込みに失敗しました。";
    }
} else {
    echo "CSVファイルを選択してください。";
}
?>
