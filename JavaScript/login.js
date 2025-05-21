'usestrict';
// script.js
document.getElementById("login_form").addEventListener("submit", function(event) {
    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;

    if (!email || !password) {
        alert("メールアドレスとパスワードは必須項目です！");
        event.preventDefault(); // フォーム送信を防止
    }
});
