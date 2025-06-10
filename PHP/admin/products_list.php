<?php 
require_once __DIR__ . '/../db_connect.php'; 

// ここで $pdo が使える状態

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
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../../CSS/admin/admin_header.css">    
    <link rel="stylesheet" href="../../CSS/admin/products_list.css">    
    
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>商品管理</h1>
            <p>登録されている商品の一覧・編集・削除が行えます。</p>
            <div class="nav-menu">
                <a href="./add_product.php">商品追加</a>
                <a href="./products_list.php">商品管理</a>
                <a href="./users_list.php">ユーザー管理</a>
                <a href="./brands_list.php">ブランド管理</a>
                <a href="../index.php">サイトに戻る</a>
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