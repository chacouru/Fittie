<?php
require_once '../db_connect.php';

$stmt = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC");
$brands = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>商品一括登録</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../../CSS/admin/admin_header.css">
    <link rel="stylesheet" href="../../CSS/admin/add_product.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <?php
            $current_page = basename($_SERVER['SCRIPT_NAME']);
            ?>
            <h1>商品追加</h1>
            <p>登録されている商品の一覧・編集・削除が行えます。</p>
                <div class="nav-menu">
                    <a href="./add_product.php" class="<?= $current_page === 'add_product.php' ? 'active' : '' ?>">商品追加</a>
                    <a href="./products_list.php" class="<?= $current_page === 'products_list.php' ? 'active' : '' ?>">商品管理</a>
                    <a href="./users_list.php" class="<?= $current_page === 'users_list.php' ? 'active' : '' ?>">ユーザー管理</a>
                    <a href="./brands_list.php" class="<?= $current_page === 'brands_list.php' ? 'active' : '' ?>">ブランド管理</a>
                    <a href="../index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">サイトに戻る</a>
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
        </form>

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