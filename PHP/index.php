<!DOCTYPE html>
<html>
<body>
<h2>ログイン</h2>
<form method="POST" action="login_function/login_handler.php">
    <input type="email" name="email" required placeholder="メールアドレス"><br>
    <input type="password" name="password" required placeholder="パスワード"><br>
    <label><input type="checkbox" name="remember_me"> ログイン状態を保持</label><br>
    <button type="submit">ログイン</button>
    <a href="./register.php"><p>新規登録はこちら</p></a>
</form>
</body>
</html>
