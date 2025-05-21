<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ログイン | fitty.</title>
        <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/login.css" />
</head>

<body>
    <div class="login_container">
        <h1>ログイン</h1>
        <form action="login.php" method="POST">
            <div class="form_group">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" required />
            </div>
            <div class="form_group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required />
            </div>
            <button type="submit">ログイン</button>
        </form>
        <p><a href="#">パスワードをお忘れですか？</a></p>
        <p><a href="./register.php">▶ 新規会員登録はこちら</a></p> <!-- 新規登録ページへのリンク -->
    </div>
</body>

</html>
