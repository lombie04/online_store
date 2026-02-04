<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();
$u = current_user();
if ($u['role'] !== 'customer') {
    redirect('/business_store/dashboard.php');
}

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
  SELECT id, status, total_amount, created_at
  FROM orders
  WHERE id = ? AND customer_id = ?
  LIMIT 1
");
$stmt->execute([$id, (int)$u['id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('/business_store/customer/orders.php');
}

$stmt = $pdo->prepare("
  SELECT oi.quantity, oi.unit_price, oi.line_total, p.name, r.store_name
  FROM order_items oi
  JOIN products p ON p.id = oi.product_id
  JOIN retailers r ON r.id = oi.retailer_id
  WHERE oi.order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$stmt = $pdo->prepare("
  SELECT old_status, new_status, changed_at
  FROM order_status_history
  WHERE order_id = ?
  ORDER BY changed_at ASC
");
$stmt->execute([$id]);
$hist = $stmt->fetchAll();
?>
<?php layout_header('Customer - Order'); ?>
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
  <p class="muted"><a href="/business_store/customer/orders.php">Back to orders</a></p>

  <div class="card">
    <h2 style="margin:0;">Order #<?php echo (int)$order['id']; ?></h2>
    <p class="muted">Status: <span class="pill"><?php echo e($order['status']); ?></span> | Total: <?php echo e(number_format((float)$order['total_amount'], 2)); ?></p>

    <h3>Items</h3>
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Store</th>
          <th>Qty</th>
          <th>Unit</th>
          <th>Line</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?php echo e($it['name']); ?></td>
          <td><?php echo e($it['store_name']); ?></td>
          <td><?php echo (int)$it['quantity']; ?></td>
          <td><?php echo e(number_format((float)$it['unit_price'], 2)); ?></td>
          <td><?php echo e(number_format((float)$it['line_total'], 2)); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <h3>Status History</h3>
    <table>
      <thead>
        <tr>
          <th>Old</th>
          <th>New</th>
          <th>Time</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($hist as $h): ?>
        <tr>
          <td><?php echo e((string)($h['old_status'] ?? '')); ?></td>
          <td><?php echo e((string)$h['new_status']); ?></td>
          <td><?php echo e((string)$h['changed_at']); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php layout_footer(); ?>
