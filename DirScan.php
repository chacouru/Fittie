<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ディレクトリ構造</title>
    <style>
        ul { list-style-type: none; padding-left: 1em; }
        li::before { content: "📁 "; }
        li.file::before { content: "📄 "; }
    </style>
</head>
<body>
    <h1>ディレクトリ構造</h1>

    <?php
    function displayDirectoryTree($dir) {
        if (!is_dir($dir)) return;

        $items = scandir($dir);
        echo "<ul>";

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            $safeItem = htmlspecialchars($item);

            if (is_dir($path)) {
                echo "<li>$safeItem";
                displayDirectoryTree($path); // 再帰的に呼び出し
                echo "</li>";
            } else {
                echo "<li class='file'>$safeItem</li>";
            }
        }

        echo "</ul>";
    }

    // ここにルートディレクトリを指定（例：'uploads'）
    $rootDir = 'PHP/img/products';
    displayDirectoryTree($rootDir);
    ?>
</body>
</html>
