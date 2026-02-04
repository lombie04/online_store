<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/cart.php';

require_login();
$u = current_user();
if ($u['role'] !== 'customer') {
    redirect('/dashboard.php');
}

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = $_POST['qty'] ?? [];
    if (is_array($updates)) {
        foreach ($updates as $pid => $qty) {
            $pid = (int)$pid;
            $qty = (int)$qty;
            cart_update($pid, $qty);
        }
    }
    redirect('/customer/cart.php');
}

$items = cart_fetch_items($pdo);

$subtotal = 0.0;
foreach ($items as $it) {
    $subtotal += $it['price'] * $it['qty'];
}
?>
<?php layout_header('Customer - Cart'); ?>
<style>
.wrap{max-width:1000px;margin:30px auto;padding:0 16px}
    .bar{background:#fff;padding:14px 16px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,.08);display:flex;justify-content:space-between;align-items:center}
    a{color:#0b5ed7;text-decoration:none}
    table{width:100%;border-collapse:collapse;background:#fff;margin-top:14px;border-radius:10px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,.08)}
    th,td{padding:10px;border-bottom:1px solid #e6e9ee;text-align:left;font-size:14px}
    th{background:#f2f4f7}
    input{width:70px;padding:8px;border:1px solid #ccd2da;border-radius:8px}
    button{padding:10px 14px;border:0;border-radius:8px;cursor:pointer;font-weight:bold}
    .row{display:flex;gap:10px;align-items:center;justify-content:flex-end;margin-top:14px}
    .muted{color:#666;font-size:13px}
</style>
<div class="card">
<div class="wrap">
  <div class="bar">
    <div>
      <div style="font-weight:bold;">Your Cart</div>
      <div class="muted"><a href="/customer/index.php">Continue shopping</a></div>
    </div>
    <div>
      <a href="/customer/orders.php">My Orders</a> |
      <a href="/logout.php">Logout</a>
    </div>
  </div>

  <?php if (count($items) === 0): ?>
    <p class="muted" style="margin-top:14px;">Your cart is empty.</p>
  <?php else: ?>
    <form method="post">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Store</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Stock</th>
            <th>Line total</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?php echo e($it['name']); ?></td>
            <td><?php echo e($it['store_name']); ?></td>
            <td><?php echo e(number_format($it['price'], 2)); ?></td>
            <td>
              <input type="number" min="0" name="qty[<?php echo (int)$it['product_id']; ?>]" value="<?php echo (int)$it['qty']; ?>">
            </td>
            <td><?php echo (int)$it['stock']; ?></td>
            <td><?php echo e(number_format($it['price'] * $it['qty'], 2)); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

      <div class="row">
        <div style="margin-right:auto;" class="muted">Set qty to 0 to remove an item.</div>
        <button type="submit">Update cart</button>
        <a href="/customer/checkout.php" style="display:inline-block;padding:10px 14px;border-radius:8px;background:#e9fff0;color:#135a2e;font-weight:bold;text-decoration:none;">Checkout</a>
      </div>

      <div class="row">
        <div style="font-weight:bold;">Subtotal: <?php echo e(number_format($subtotal, 2)); ?></div>
      </div>
    </form>
  <?php endif; ?>
</div>
</div>
<?php layout_footer(); ?>

