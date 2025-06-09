document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.increase').forEach(btn => {
    btn.addEventListener('click', () => changeQuantity(btn, +1));
  });

  document.querySelectorAll('.decrease').forEach(btn => {
    btn.addEventListener('click', () => changeQuantity(btn, -1));
  });

  document.querySelectorAll('.delete').forEach(btn => {
    btn.addEventListener('click', () => deleteItem(btn));
  });

  document.getElementById('checkout-btn')?.addEventListener('click', () => {
    location.href = 'checkout.php';
  });
});

function changeQuantity(button, diff) {
  const row = button.closest('tr');
  const cartId = row.dataset.cartId;
  const stock = parseInt(row.dataset.stock);
  const quantityEl = row.querySelector('.quantity');
  let quantity = parseInt(quantityEl.textContent) + diff;

  if (quantity < 1 || quantity > stock) return;

  fetch('api/update_cart_quantity.php', {
    method: 'POST',
    body: new URLSearchParams({ cart_id: cartId, quantity })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      quantityEl.textContent = quantity;
      row.querySelector('.subtotal').textContent = (data.price * quantity) + '円';
    }
  });
}

function deleteItem(button) {
  const row = button.closest('tr');
  const cartId = row.dataset.cartId;

  fetch('api/delete_cart_item.php', {
    method: 'POST',
    body: new URLSearchParams({ cart_id: cartId })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      row.remove();
    }
  });
}
