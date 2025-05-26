<?php
// エラー表示用
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // データ受け取り
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $category = $_POST['category'] ?? '';
    $image = $_FILES['image'] ?? null;

    // バリデーション
    if ($name === '' || $price === '' || $stock === '') {
        $error = '商品名、価格、在庫数は必須です。';
    } else {
        // 画像の処理（仮）
        $image_path = '';
        if ($image && $image['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            $filename = basename($image['name']);
            $target = $upload_dir . $filename;
            if (move_uploaded_file($image['tmp_name'], $target)) {
                $image_path = $target;
            } else {
                $error = '画像のアップロードに失敗しました。';
            }
        }

        if ($error === '') {
            // DB接続（仮）
            $pdo = new PDO('mysql:host=localhost;dbname=ec_db;charset=utf8', 'root', '');
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category, image_path)
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $stock, $category, $image_path]);
            $success = '商品を登録しました！';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品登録 - 管理者ページ</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f9f9f9;
            padding: 2rem;
        }
        form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            width: 400px;
            margin: auto;
        }
        input, textarea, select {
            width: 100%;
            margin-bottom: 1rem;
            padding: 0.5rem;
        }
        button {
            padding: 0.5rem 1rem;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
        }
        .message {
            text-align: center;
            margin-bottom: 1rem;
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
    <form action="" method="post" enctype="multipart/form-data">
        <h2>商品登録</h2>

        <?php if ($error): ?>
            <div class="message"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <label>商品名 *</label>
        <input type="text" name="name" required>

        <label>商品説明</label>
        <textarea name="description" rows="4"></textarea>

        <label>価格（円） *</label>
        <input type="number" name="price" required>

        <label>在庫数 *</label>
        <input type="number" name="stock" required>

        <label>カテゴリ</label>
        <input type="text" name="category">

        <label>商品画像</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit">登録する</button>
    </form>
</body>
</html>
