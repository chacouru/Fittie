<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['products'])) {
    $products = $_POST['products'];
    $pdo = new PDO('mysql:host=localhost;dbname=fitty;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $count = 0;

    foreach ($products as $product) {
        if (empty($product['name']) || !is_numeric($product['price']) || !is_numeric($product['stock'])) continue;

        $name = $product['name'];
        $description = $product['description'] ?? '';
        $price = $product['price'];
        $category_id = $product['category_id'] ?? null;
        $stock = $product['stock'];
        $is_on_sale = isset($product['is_on_sale']) ? 1 : 0;
        $sale_price = $product['sale_price'] ?? null;
        $image = $product['image'] ?? null;
        $brand_id = $product['brand_id'] ?? null;
        $is_active = isset($product['is_active']) ? (int)$product['is_active'] : 1;

        $stmt = $pdo->prepare("INSERT INTO products 
            (name, description, price, image, category_id, stock, brand_id, is_on_sale, sale_price, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $image, $category_id, $stock, $brand_id, $is_on_sale, $sale_price, $is_active]);
        $count++;
    }

    echo "{$count} 件の商品をフォームから登録しました。";
} else {
    echo "無効なデータです。";
}
?>
