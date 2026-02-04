<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_approved_retailer();
$u = current_user();

$pdo = db();

$stmt = $pdo->prepare("SELECT id, store_name FROM retailers WHERE user_id = ? LIMIT 1");
$stmt->execute([(int)$u['id']]);
$retailer = $stmt->fetch();
$retailerId = $retailer ? (int)$retailer['id'] : 0;
if ($retailerId <= 0) redirect('/retailer/pending.php');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('/retailer/orders.php');

$stmt = $pdo->prepare("
  SELECT DISTINCT o.id, o.status, o.total_amount, o.created_at
  FROM orders o
  JOIN order_items oi ON oi.order_id = o.id
  WHERE o.id = ? AND oi.retailer_id = ?
  LIMIT 1
");
$stmt->execute([$id, $retailerId]);
$order = $stmt->fetch();
if (!$order) redirect('/retailer/orders.php');

$stmt = $pdo->prepare("
  SELECT oi.quantity, oi.unit_price, oi.line_total, p.name
  FROM order_items oi
  JOIN products p ON p.id = oi.product_id
  WHERE oi.order_id = ? AND oi.retailer_id = ?
");
$stmt->execute([$id, $retailerId]);
$items = $stmt->fetchAll();

$subtotal = 0.0;
foreach ($items as $it) $subtotal += (float)$it['line_total'];
?>
<?php layout_header('Retailer - Order'); ?>
<style>
.wrap{max-width:1000px;margin:30px auto;padding:0 16px}
    .card{background:#fff;padding:16px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,.08);margin-top:14px}
    a{color:#0b5ed7;text-decoration:none}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{padding:10px;border-bottom:1px solid #e6e9ee;text-align:left;font-size:14px}
    th{background:#f2f4f7}
    .pill{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;background:#eef2f7}
    .muted{color:#666;font-size:13px}
</style>
<div class="wrap">
  <p class="muted"><a href="/retailer/orders.php">Back to orders</a></p>

  <div class="card">
    <h2 style="margin:0;">Order #<?php echo (int)$order['id']; ?></h2>
    <p class="muted">
      Order status: <span class="pill"><?php echo e($order['status']); ?></span>
      | Your items subtotal: <?php echo e(number_format($subtotal, 2)); ?>
    </p>

    <h3>Your items in this order</h3>
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
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?php echo e($it['name']); ?></td>
          <td><?php echo (int)$it['quantity']; ?></td>
          <td><?php echo e(number_format((float)$it['unit_price'], 2)); ?></td>
          <td><?php echo e(number_format((float)$it['line_total'], 2)); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php layout_footer(); ?>

