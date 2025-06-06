<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, 'r')) !== false) {
        $pdo = new PDO('mysql:host=localhost;dbname=fitty;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $header = fgetcsv($handle); // ヘッダー読み飛ばし
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6) continue;
            [$name, $description, $price, $category_id, $stock, $is_on_sale, $sale_price] = array_pad($row, 7, null);

            $stmt = $pdo->prepare("INSERT INTO products 
                (name, description, price, category_id, stock, is_on_sale, sale_price) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $category_id, $stock, $is_on_sale, $sale_price]);
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
