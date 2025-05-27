<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>fitty. | 新規登録</title>
    <link rel="stylesheet" href="../CSS/register.css">
</head>
<body>
    <main>
        <h2>新規登録</h2>
        <form method="POST" action="login_function/register_handler.php">
            <input type="text" name="name" required placeholder="名前">
            <input type="email" name="email" required placeholder="メール">
            <input type="password" name="password" required placeholder="パスワード">
            <input type="text" name="address" placeholder="住所">
            <input type="text" name="phone" placeholder="電話番号">
            <button type="submit">登録</button>
        </form>
    </main>
</body>
</html>
