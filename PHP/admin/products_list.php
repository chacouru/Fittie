<?php
require_once __DIR__ . '/../DbManager.php';
// require_once __DIR__ . '/../login_function/functions.php';
// $user_id = check_admin_login(); // 管理者ログイン確認

$pdo = getDb();

// 商品一覧を取得
$sql = "SELECT 
          p.id, 
          p.name, 
          p.description, 
          p.price, 
          p.image, 
          p.stock, 
          p.is_active,
          p.created_at,
          c.name AS category_name,
          b.name AS brand_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN brands b ON p.brand_id = b.id
        ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品一覧 - 管理者ページ</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #2d3436;
            margin-bottom: 10px;
        }
        
        .nav-menu {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        
        .nav-menu a {
            padding: 8px 16px;
            background: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .nav-menu a:hover {
            background: #0052a3;
        }
        
        .products-table {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2d3436;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .status-active {
            color: #00b894;
            font-weight: 600;
        }
        
        .status-inactive {
            color: #d63031;
            font-weight: 600;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
        }
        
        .btn-edit {
            background: #0066cc;
            color: white;
        }
        
        .btn-delete {
            background: #d63031;
            color: white;
        }
        
        .btn-toggle {
            background: #fdcb6e;
            color: #2d3436;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #636e72;
        }
        
        .price {
            font-weight: 600;
            color: #2d3436;
        }
        
        .stock-low {
            color: #d63031;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>商品管理</h1>
            <p>登録されている商品の一覧・編集・削除が行えます。</p>
            <div class="nav-menu">
                <a href="./add_product.php">商品追加</a>
                <a href="./users_list.php">ユーザー管理</a>
                <a href="./brands_list.php">ブランド管理</a>
                <a href="../cart_preview.php">サイトに戻る</a>
            </div>
        </div>
        
        <div class="products-table">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <h3>商品がありません</h3>
                    <p>商品を追加してください。</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>画像</th>
                            <th>商品名</th>
                            <th>ブランド</th>
                            <th>カテゴリ</th>
                            <th>価格</th>
                            <th>在庫</th>
                            <th>状態</th>
                            <th>登録日</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['id']) ?></td>
                                <td>
                                    <?php if ($product['image']): ?>
                                        <img src="../img/products/<?= htmlspecialchars($product['brand_name']) ?>/<?= htmlspecialchars($product['image']) ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>" 
                                             class="product-image"
                                             onerror="this.style.display='none'">
                                    <?php else: ?>
                                        <div style="width:60px;height:60px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:4px;font-size:12px;color:#999;">画像なし</div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars($product['brand_name'] ?? '未設定') ?></td>
                                <td><?= htmlspecialchars($product['category_name'] ?? '未設定') ?></td>
                                <td class="price">¥<?= number_format($product['price']) ?></td>
                                <td class="<?= $product['stock'] <= 5 ? 'stock-low' : '' ?>">
                                    <?= $product['stock'] ?>
                                    <?= $product['stock'] <= 5 ? ' (少)' : '' ?>
                                </td>
                                <td class="<?= $product['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                    <?= $product['is_active'] ? '公開中' : '非公開' ?>
                                </td>
                                <td><?= date('Y/m/d', strtotime($product['created_at'])) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-edit">編集</a>
                                        <button onclick="toggleStatus(<?= $product['id'] ?>, <?= $product['is_active'] ? 0 : 1 ?>)" 
                                                class="btn btn-toggle">
                                            <?= $product['is_active'] ? '非公開' : '公開' ?>
                                        </button>
                                        <button onclick="deleteProduct(<?= $product['id'] ?>)" class="btn btn-delete">削除</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleStatus(productId, newStatus) {
            if (confirm(newStatus ? '商品を公開しますか？' : '商品を非公開にしますか？')) {
                fetch('toggle_product_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&status=${newStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('エラーが発生しました');
                    }
                });
            }
        }

        function deleteProduct(productId) {
            if (confirm('本当に削除しますか？この操作は取り消せません。')) {
                fetch('delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('削除に失敗しました');
                    }
                });
            }
        }
    </script>
</body>
</html>