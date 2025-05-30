<?php
require_once __DIR__ . '/../DbManager.php';
// require_once __DIR__ . '/../login_function/functions.php';
// $user_id = check_admin_login(); // 管理者ログイン確認

$pdo = getDb();

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
            max-width: 1400px;
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
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #0066cc;
        }
        
        .stat-label {
            color: #636e72;
            margin-top: 5px;
        }
        
        .users-table {
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
        
        .user-email {
            color: #0066cc;
        }
        
        .user-stats {
            font-size: 14px;
            color: #636e72;
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
        
        .btn-view {
            background: #00b894;
            color: white;
        }
        
        .btn-edit {
            background: #0066cc;
            color: white;
        }
        
        .btn-delete {
            background: #d63031;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #636e72;
        }
        
        .search-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .high-value-user {
            background: #fff3e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ユーザー管理</h1>
            <p>登録されているユーザーの一覧・詳細確認が行えます。</p>
            <div class="nav-menu">
                <a href="./products_list.php">商品管理</a>
                <a href="./add_product.php">商品追加</a>
                <a href="./brands_list.php">ブランド管理</a>
                <a href="../cart_preview.php">サイトに戻る</a>
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