<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>探す</title>
            <link rel="stylesheet" href="../CSS/reset.css">
    <link rel="stylesheet" href="../CSS/search.css">
</head>

<body>
    
    <main>
        
        <form action="" class="form_box">
            <h1>探す</h1>
        <div class="form_container">
        <div class="select_container">
            <select name="brand">
                <option value="" selected disabled hidden>ブランド</option>
                <option value="1">HARE</option>
                <option value="2">ONCILY</option>
                <option value="3">LACOSTE</option>
            </select>
        </div>
        <div class="select_container">
            <select name="color">
                <option value="" selected disabled hidden>カラー</option>
                <option value="1">ブラック</option>
                <option value="2">ホワイト</option>
                <option value="3">レッド</option>
            </select>
        </div>
        <div class="select_container">
            <select name="genre">
                <option value="" selected disabled hidden>ジャンル</option>
                <option value="1">トップス</option>
                <option value="2">パンツ</option>
                <option value="3">シューズ</option>
            </select>
        </div>
        </div>
         <div class="button_container">
        <input type="reset" value="リセット">
        <button type="submit">この条件で探す</button>
          </div>
          </form>
    </main>
</body>

</html>