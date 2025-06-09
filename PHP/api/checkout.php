<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>注文確認</title>
</head>
<body>
  <h1>注文を確認してください</h1>
  <p>ここに注文内容を表示します（今はダミー）</p>
  <a href="complete.php"><button>購入を確定</button></a>
</body>
</html>
