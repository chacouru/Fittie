<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品一括登録</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f2f4f8;
            margin: 0;
            padding: 2rem;
        }
        .container {
            background: #fff;
            max-width: 960px;
            margin: auto;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 2rem;
        }
        h2 {
            border-left: 6px solid #007bff;
            padding-left: 0.5rem;
            color: #007bff;
            margin-top: 2.5rem;
        }
        form {
            margin-top: 1rem;
        }
        input[type="file"],
        input[type="text"],
        input[type="number"],
        input[type="checkbox"] {
            padding: 0.5rem;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th {
            background: #007bff;
            color: white;
            padding: 0.75rem;
            font-weight: bold;
        }
        td {
            padding: 0.5rem;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        button {
            background: #007bff;
            color: white;
            padding: 0.6rem 1.5rem;
            font-size: 1rem;
            border: none;
            border-radius: 6px;
            margin-top: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        .note {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>商品一括登録</h1>

    <h2>方法1 CSVアップロード</h2>
    <form action="handle_csv_upload.php" method="post" enctype="multipart/form-data">
        <label for="csv_file">CSVファイルを選択：</label>
        <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
        <button type="submit">CSVを登録</button>
        <p class="note">※ フォーマット: 商品名,説明,価格,カテゴリID,在庫,セール中,セール価格</p>
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
                <th>セール中</th>
                <th>セール価格</th>
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
                    <td style="text-align: center;"><input type="checkbox" name="products[<?= $i ?>][is_on_sale]" value="1"></td>
                    <td><input type="number" name="products[<?= $i ?>][sale_price]"></td>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
        <button type="submit">フォームから登録</button>
    </form>
</div>
</body>
</html>
