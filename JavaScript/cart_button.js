document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.add-to-cart-btn').forEach(function (button) {
    button.addEventListener('click', function () {
      console.log('ボタン押されたで'); // 開発者ツールのConsoleで確認用

      const container = this.closest('.cart-button-container');
      const formData = new FormData();
      formData.append('id', container.dataset.id);

      fetch('add_to_cart.php', {  // 必要に応じて 'api/add_to_cart.php' に変更
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        const msg = container.querySelector('.cart-message');
        if (data.success) {
          msg.textContent = 'カートに追加しました';
          msg.style.color = 'green';
        } else {
          msg.textContent = data.message || 'エラーが発生しました';
          msg.style.color = 'red';
        }
      })
      .catch(err => {
        console.error('Fetch error:', err);
      });
    });
  });
});
