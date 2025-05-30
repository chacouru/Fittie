<?php
require_once '../DbManager.php'; // パスはプロジェクト構成に合わせて変更してな

$db = getDb(); // DbManager を呼び出す
$stmt = $db->query("SELECT id, name FROM brands ORDER BY name ASC");
$brands = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>商品一括登録</title>
    <link rel="stylesheet" href="../../CSS/reset.css">
    <link rel="stylesheet" href="../../CSS/add_product.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>ブランド管理</h1>
            <p>商品ブランドの追加・管理が行えます。</p>
            <div class="nav-menu">
                <a href="./products_list.php">商品管理</a>
                <a href="./add_product.php">商品追加</a>
                <a href="./users_list.php">ユーザー管理</a>
                <a href="../cart_preview.php">サイトに戻る</a>
            </div>
        </div>

        <h1>商品一括登録</h1>

        <h2>方法1 CSVアップロード</h2>
        <form action="handle_csv_upload.php" method="post" enctype="multipart/form-data">
            <label for="csv_file">CSVファイルを選択：</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
            <button type="submit">CSVを登録</button>
            <p class="note">※ フォーマット: 商品名,説明,価格,カテゴリID,在庫,ブランドID,画像ファイル名<br>
                例）<br>
                Tシャツ,白色のTシャツ,1800,1,20,2,tshirt.jpg <br>
                バッグ,レザーショルダー,8000,3,5,1,bag.jpg
            </p>

            <h2>方法2 フォームで複数登録（最大5件）</h2>
            <form action="handle_multiple_form.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <th>商品名</th>
                            <th>説明</th>
                            <th>価格</th>
                            <th>カテゴリID</th>
                            <th>在庫</th>
                            <th>ブランド</th>
                            <th>画像ファイル名</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <tr>
                                <td><input type="text" name="products[<?= $i ?>][name]"></td>
                                <td><input type="text" name="products[<?= $i ?>][description]"></td>
                                <td><input type="number" name="products[<?= $i ?>][price]"></td>
                                <td><input type="number" name="products[<?= $i ?>][category_id]"></td>
                                <td><input type="number" name="products[<?= $i ?>][stock]"></td>
                                <td>
                                    <select name="products[<?= $i ?>][brand_id]">
                                        <option value="">選択してください</option>
                                        <?php foreach ($brands as $brand): ?>
                                            <option value="<?= htmlspecialchars($brand['id']) ?>">
                                                <?= htmlspecialchars($brand['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="text" name="products[<?= $i ?>][image]" placeholder="例: bag1.jpg"></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <button type="submit">フォームから登録</button>
            </form>
    </div>
</body>

</html>