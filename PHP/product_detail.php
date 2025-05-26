<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=fitty;charset=utf8', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 商品IDを取得（GETパラメータ）
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // IDが正しくない場合は終了
    if ($id <= 0) {
        throw new Exception("不正な商品IDです。");
    }

    // 商品を1件取得
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("商品が見つかりませんでした。");
    }

} catch (Exception $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> | 商品詳細</title>
    <link rel="stylesheet" href="../CSS/products.css">
</head>
<body>
    <div id="title">
        <h1>Fashion Store</h1>
    </div>
    <main class="product_detail">
    <div class="product_image">
        <img src="./img/products/ <?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
    </div>
    <div class="product_info">
        <h2><?= htmlspecialchars($product['name']) ?></h2>
        <p class="price">¥<?= number_format($product['price']) ?></p>
        <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <button>カートに追加</button>
    </div>
</main>

</body>
</html>
