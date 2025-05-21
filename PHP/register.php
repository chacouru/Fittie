<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>新規登録 | fitty.</title>
        <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/login.css" />
</head>

<body>
    <div class="login_container">
        <h1>新規会員登録</h1>
        <form action="register.php" method="POST">
            <div class="form_group">
                <label for="name">お名前</label>
                <input type="text" id="name" name="name" required />
            </div>
            <div class="form_group">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" required />
            </div>
            <div class="form_group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required />
            </div>
            <div class="form_group">
                <label for="confirm_password">パスワード（確認）</label>
                <input type="password" id="confirm_password" name="confirm_password" required />
            </div>
            <button type="submit">登録する</button>
        </form>
        <p><a href="./login.php">▶ すでにアカウントをお持ちの方はこちら</a></p>
    </div>
</body>

</html>
