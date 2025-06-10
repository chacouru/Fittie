document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', () => {
            const payload = {
                product_id: button.dataset.id
            };

            fetch('api/add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('カートに追加しました');
                } else {
                    alert('エラー: ' + data.message);
                }
            })
            .catch(() => alert('通信エラーが発生しました。'));
        });
    });
});
