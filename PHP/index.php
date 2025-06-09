<?php
session_start();
require_once 'DbManager.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = getDb();

$sql = 'SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.image, p.brand, p.stock
        FROM cart_items c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?';
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ショッピングカート</title>
  <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
  <h1>ショッピングカート</h1>

  <div id="cart-container">
    <?php if (empty($items)): ?>
      <p>カートに商品はありません。</p>
    <?php else: ?>
      <?php foreach ($items as $item): ?>
        <?php
          // ブランドフォルダをサニタイズ
          $safe_brand_folder = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $item['brand']);
          $image_file = $item['image'] ?? 'no-image.png';

          // 画像パス候補
          $possible_paths = [
              "../PHP/img/products/{$safe_brand_folder}/{$image_file}",
              "../PHP/img/products/default/{$image_file}",
              "../PHP/img/products/{$image_file}",
              "../PHP/img/no-image.png"
          ];

          $image_path = "../PHP/img/no-image.png";
          foreach ($possible_paths as $path) {
              if (file_exists($path)) {
                  $image_path = $path;
                  break;
              }
          }
        ?>
        <div class="cart-item" data-cart-id="<?= $item['cart_id'] ?>" data-stock="<?= $item['stock'] ?>">
          <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-product-image">
          <div class="cart-item-details">
            <h2><?= htmlspecialchars($item['name']) ?></h2>
            <div class="cart-price">価格：<?= $item['price'] ?>円</div>
            <div class="cart-controls">
              <button class="decrease">−</button>
              <span class="quantity"><?= $item['quantity'] ?></span>
              <button class="increase">＋</button>
              <div class="cart-subtotal"><?= $item['price'] * $item['quantity'] ?>円</div>
              <button class="delete">削除</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="cart-footer">
        <h2>合計金額：<span id="total-price">計算中...</span></h2>
        <button id="checkout-btn">レジに進む</button>
      </div>
    <?php endif; ?>
  </div>

  <script src="../JavaScript/cart_page.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      function updateTotal() {
        let total = 0;
        document.querySelectorAll('.cart-subtotal').forEach(el => {
          total += parseInt(el.textContent.replace('円', ''));
        });
        document.getElementById('total-price').textContent = total + '円';
      }
      updateTotal();
    });
  </script>
</body>
</html>
