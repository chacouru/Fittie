<?php
require_once __DIR__ . '/../db_connect.php';  // DbManager.php → db_connect.php に変更

// $pdo は db_connect.php 内で作成済み

// ユーザー一覧を取得（購入履歴数も含む）
$sql = "SELECT 
          u.id,
          u.name,
          u.email,
          u.created_at,
          u.address,
          u.phone,
          COUNT(o.id) as order_count,
          COALESCE(SUM(o.total_price), 0) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー管理 - 管理者ページ</title>
        <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../../CSS/admin/admin_header.css">    
    <link rel="stylesheet" href="../../CSS/admin/users_list.css">    
  
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ユーザー管理</h1>
            <p>登録されている商品の一覧・編集・削除が行えます。</p>
            <div class="nav-menu">
                <a href="./add_product.php">商品追加</a>
                <a href="./products_list.php">商品管理</a>
                <a href="./users_list.php">ユーザー管理</a>
                <a href="./brands_list.php">ブランド管理</a>
                <a href="../index.php">サイトに戻る</a>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= count($users) ?></div>
                <div class="stat-label">総ユーザー数</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($users, function($u) { return $u['order_count'] > 0; })) ?></div>
                <div class="stat-label">購入経験ユーザー</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">¥<?= number_format(array_sum(array_column($users, 'total_spent'))) ?></div>
                <div class="stat-label">総売上</div>
            </div>
        </div>
        
        <div class="search-box">
            <input type="text" class="search-input" placeholder="ユーザー名またはメールアドレスで検索..." onkeyup="filterUsers(this.value)">
        </div>
        
        <div class="users-table">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <h3>ユーザーがいません</h3>
                    <p>まだユーザーが登録されていません。</p>
                </div>
            <?php else: ?>
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ユーザー名</th>
                            <th>メールアドレス</th>
                            <th>登録日</th>
                            <th>住所</th>
                            <th>電話番号</th>
                            <th>注文回数</th>
                            <th>総購入額</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="user-row <?= $user['total_spent'] > 50000 ? 'high-value-user' : '' ?>" data-name="<?= strtolower($user['name']) ?>" data-email="<?= strtolower($user['email']) ?>">
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td class="user-email"><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= date('Y/m/d', strtotime($user['created_at'])) ?></td>
                                <td><?= htmlspecialchars($user['address'] ?? '未登録') ?></td>
                                <td><?= htmlspecialchars($user['phone'] ?? '未登録') ?></td>
                                <td><?= $user['order_count'] ?>回</td>
                                <td class="user-stats">¥<?= number_format($user['total_spent']) ?></td>
                                <td>
                                    <div class="actions">
                                        <button onclick="viewUserDetails(<?= $user['id'] ?>)" class="btn btn-view">詳細</button>
                                        <button onclick="editUser(<?= $user['id'] ?>)" class="btn btn-edit">編集</button>
                                        <button onclick="deleteUser(<?= $user['id'] ?>)" class="btn btn-delete">削除</button>
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
        function filterUsers(searchTerm) {
            const rows = document.querySelectorAll('.user-row');
            const term = searchTerm.toLowerCase();
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const email = row.getAttribute('data-email');
                
                if (name.includes(term) || email.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function viewUserDetails(userId) {
            // ユーザー詳細モーダルを表示（実装は省略）
            alert(`ユーザーID: ${userId} の詳細表示機能は今後実装予定です`);
        }

        function editUser(userId) {
            // ユーザー編集ページへ遷移
            window.location.href = `edit_user.php?id=${userId}`;
        }

        function deleteUser(userId) {
            if (confirm('本当にこのユーザーを削除しますか？関連する注文履歴も削除されます。')) {
                fetch('delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('削除に失敗しました: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>