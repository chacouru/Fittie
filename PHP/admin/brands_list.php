<?php
require_once '../db_connect.php';

$stmt = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC");
$brands = $stmt->fetchAll();


// ブランド追加処理
if ($_POST && isset($_POST['add_brand'])) {
    $brand_name = trim($_POST['brand_name']);

    if (!empty($brand_name)) {
        try {
            $sql = "INSERT INTO brands (name) VALUES (:name)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $brand_name);
            $stmt->execute();
            $success_message = "ブランド「{$brand_name}」を追加しました。";
        } catch (PDOException $e) {
            $error_message = "ブランドの追加に失敗しました。";
        }
    } else {
        $error_message = "ブランド名を入力してください。";
    }
}

// ブランド削除処理
if ($_POST && isset($_POST['delete_brand'])) {
    $brand_id = $_POST['brand_id'];

    try {
        // まず関連する商品があるかチェック
        $check_sql = "SELECT COUNT(*) as count FROM products WHERE brand_id = :brand_id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->bindParam(':brand_id', $brand_id);
        $check_stmt->execute();
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            $error_message = "このブランドに関連する商品があるため削除できません。";
        } else {
            $sql = "DELETE FROM brands WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $brand_id);
            $stmt->execute();
            $success_message = "ブランドを削除しました。";
        }
    } catch (PDOException $e) {
        $error_message = "ブランドの削除に失敗しました。";
    }
}

// ブランド一覧を取得（商品数も含む）
$sql = "SELECT 
          b.id,
          b.name,
          b.created_at,
          COUNT(p.id) as product_count
        FROM brands b
        LEFT JOIN products p ON b.id = p.brand_id
        GROUP BY b.id
        ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ブランド管理 - 管理者ページ</title>
    <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../../CSS/admin/admin_header.css">
    <link rel="stylesheet" href="../../CSS/admin/brands_list.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>ブランド管理</h1>
            <p>登録されている商品の一覧・編集・削除が行えます。</p>
            <div class="nav-menu">
                <a href="./add_product.php">商品追加</a>
                <a href="./products_list.php">商品管理</a>
                <a href="./users_list.php">ユーザー管理</a>
                <a href="./brands_list.php">ブランド管理</a>
                <a href="../index.php">サイトに戻る</a>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="add-brand-form">
            <h2>新しいブランドを追加</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="brand_name">ブランド名</label>
                    <input type="text" id="brand_name" name="brand_name" required placeholder="ブランド名を入力してください">
                </div>
                <button type="submit" name="add_brand" class="btn-primary">ブランドを追加</button>
            </form>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= count($brands) ?></div>
                <div class="stat-label">総ブランド数</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= array_sum(array_column($brands, 'product_count')) ?></div>
                <div class="stat-label">総商品数</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($brands, function ($b) {
                                                return $b['product_count'] > 0;
                                            })) ?></div>
                <div class="stat-label">商品有りブランド</div>
            </div>
        </div>

        <div class="search-box">
            <input type="text" class="search-input" placeholder="ブランド名で検索..." onkeyup="filterBrands(this.value)">
        </div>

        <div class="brands-table">
            <?php if (empty($brands)): ?>
                <div class="empty-state">
                    <h3>ブランドがありません</h3>
                    <p>まだブランドが登録されていません。上記のフォームから新しいブランドを追加してください。</p>
                </div>
            <?php else: ?>
                <table id="brandsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ブランド名</th>
                            <th>商品数</th>
                            <th>登録日</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($brands as $brand): ?>
                            <tr class="brand-row" data-name="<?= strtolower($brand['name']) ?>">
                                <td><?= htmlspecialchars($brand['id']) ?></td>
                                <td class="brand-name"><?= htmlspecialchars($brand['name']) ?></td>
                                <td>
                                    <span class="product-count"><?= $brand['product_count'] ?>個</span>
                                </td>
                                <td><?= date('Y/m/d', strtotime($brand['created_at'])) ?></td>
                                <td>
                                    <div class="actions">
                                        <button onclick="editBrand(<?= $brand['id'] ?>, '<?= htmlspecialchars($brand['name'], ENT_QUOTES) ?>')" class="btn btn-edit">編集</button>
                                        <button onclick="deleteBrand(<?= $brand['id'] ?>, '<?= htmlspecialchars($brand['name'], ENT_QUOTES) ?>', <?= $brand['product_count'] ?>)" class="btn btn-delete">削除</button>
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
        function filterBrands(searchTerm) {
            const rows = document.querySelectorAll('.brand-row');
            const term = searchTerm.toLowerCase();

            rows.forEach(row => {
                const name = row.getAttribute('data-name');

                if (name.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function editBrand(brandId, brandName) {
            const newName = prompt('新しいブランド名を入力してください:', brandName);
            if (newName && newName !== brandName) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="edit_brand" value="1">
                    <input type="hidden" name="brand_id" value="${brandId}">
                    <input type="hidden" name="brand_name" value="${newName}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteBrand(brandId, brandName, productCount) {
            if (productCount > 0) {
                alert(`「${brandName}」には${productCount}個の商品が関連付けられているため削除できません。\n先に関連商品を削除または他のブランドに変更してください。`);
                return;
            }

            if (confirm(`ブランド「${brandName}」を削除してもよろしいですか？`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="delete_brand" value="1">
                    <input type="hidden" name="brand_id" value="${brandId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>