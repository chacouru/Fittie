<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=fitty;charset=utf8', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // 商品一覧を取得
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "データベース接続エラー: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品一覧</title>
    <link rel="stylesheet" href="../CSS/products.css">
</head>
<body>

    
    <main class="product_list">
    <div id="title">
        <h1>Fashion Store</h1>
    </div>
    <h2>商品一覧</h2>
    <div class="products">
        <?php foreach ($products as $row): ?>
            <div class="product_card">
                <img src="./img/products/ <?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p>¥<?= number_format($row['price']) ?></p>
                <a href="product_detail.php?id=<?= $row['id'] ?>">詳細を見る</a>
            </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>
