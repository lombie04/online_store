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
$items = cart_fetch_items($pdo);

if (count($items) === 0) {
    redirect('/customer/cart.php');
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        $total = 0.0;
        $stmt = $pdo->prepare("INSERT INTO orders (customer_id, status, total_amount) VALUES (?, 'placed', 0.00)");
        $stmt->execute([(int)$u['id']]);
        $orderId = (int)$pdo->lastInsertId();

        foreach ($items as $it) {
            $pid = (int)$it['product_id'];
            $qty = (int)$it['qty'];
            $stmt = $pdo->prepare("
              SELECT p.id, p.price, p.stock, p.retailer_id, p.is_active, r.approval_status
              FROM products p
              JOIN retailers r ON r.id = p.retailer_id
              WHERE p.id = ?
              FOR UPDATE
            ");
            $stmt->execute([$pid]);
            $p = $stmt->fetch();

            if (!$p) {
                throw new RuntimeException("A product in your cart no longer exists.");
            }
            if ((int)$p['is_active'] !== 1 || $p['approval_status'] !== 'approved') {
                throw new RuntimeException("A product in your cart is no longer available.");
            }
            if ((int)$p['stock'] < $qty) {
                throw new RuntimeException("Not enough stock for: " . $it['name']);
            }

            $unitPrice = (float)$p['price'];
            $lineTotal = $unitPrice * $qty;
            $total += $lineTotal;
            $stmt = $pdo->prepare("
              INSERT INTO order_items (order_id, product_id, retailer_id, quantity, unit_price, line_total)
              VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orderId,
                $pid,
                (int)$p['retailer_id'],
                $qty,
                $unitPrice,
                $lineTotal
            ]);
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$qty, $pid]);
        }
        $stmt = $pdo->prepare("UPDATE orders SET total_amount = ? WHERE id = ?");
        $stmt->execute([$total, $orderId]);
        $stmt = $pdo->prepare("
          INSERT INTO order_status_history (order_id, old_status, new_status, changed_by_user_id)
          VALUES (?, NULL, 'placed', ?)
        ");
        $stmt->execute([$orderId, (int)$u['id']]);

        $pdo->commit();

        cart_clear();
        redirect('/customer/order.php?id=' . $orderId);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}
?>
<?php layout_header('Customer - Checkout'); ?>
<style>
.wrap{max-width:900px;margin:30px auto;padding:0 16px}
    .card{background:#fff;padding:16px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,.08)}
    a{color:#0b5ed7;text-decoration:none}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{padding:10px;border-bottom:1px solid #e6e9ee;text-align:left;font-size:14px}
    th{background:#f2f4f7}
    .msg{padding:10px;border-radius:8px;margin-top:12px}
    .err{background:#ffe8e8;color:#7a1d1d}
    button{margin-top:14px;padding:10px 14px;border:0;border-radius:8px;cursor:pointer;font-weight:bold}
    .muted{color:#666;font-size:13px}
</style>
<div class="wrap">
  <p class="muted"><a href="/customer/cart.php">Back to cart</a></p>

  <div class="card">
    <h2 style="margin:0;">Checkout</h2>

    <?php if ($error !== ""): ?><div class="msg err"><?php echo e($error); ?></div><?php endif; ?>

    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Qty</th>
          <th>Unit</th>
          <th>Line</th>
        </tr>
      </thead>
      <tbody>
      <?php $total = 0.0; foreach ($items as $it): $line = $it['price'] * $it['qty']; $total += $line; ?>
        <tr>
          <td><?php echo e($it['name']); ?></td>
          <td><?php echo (int)$it['qty']; ?></td>
          <td><?php echo e(number_format($it['price'], 2)); ?></td>
          <td><?php echo e(number_format($line, 2)); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <p style="font-weight:bold;margin-top:12px;">Total: <?php echo e(number_format($total, 2)); ?></p>

    <form method="post">
      <button type="submit">Place order</button>
    </form>
  </div>
</div>
<?php layout_footer(); ?>

