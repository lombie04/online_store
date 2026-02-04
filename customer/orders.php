<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();
$u = current_user();
if ($u['role'] !== 'customer') {
    redirect('/dashboard.php');
}

$pdo = db();
$stmt = $pdo->prepare("
  SELECT id, status, total_amount, created_at
  FROM orders
  WHERE customer_id = ?
  ORDER BY created_at DESC
");
$stmt->execute([(int)$u['id']]);
$orders = $stmt->fetchAll();
?>
<?php layout_header('Customer - Orders'); ?>
<style>
.wrap{max-width:1000px;margin:30px auto;padding:0 16px}
    .bar{background:#fff;padding:14px 16px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,.08);display:flex;justify-content:space-between;align-items:center}
    a{color:#0b5ed7;text-decoration:none}
    table{width:100%;border-collapse:collapse;background:#fff;margin-top:14px;border-radius:10px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,.08)}
    th,td{padding:10px;border-bottom:1px solid #e6e9ee;text-align:left;font-size:14px}
    th{background:#f2f4f7}
    .pill{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;background:#eef2f7}
    .muted{color:#666;font-size:13px}
</style>
<div class="card">
<div class="wrap">
  <div class="bar">
    <div>
      <div style="font-weight:bold;">My Orders</div>
      <div class="muted"><a href="/customer/index.php">Storefront</a></div>
    </div>
    <div>
      <a href="/customer/cart.php">Cart</a> |
      <a href="/logout.php">Logout</a>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Order</th>
        <th>Status</th>
        <th>Total</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td><a href="/customer/order.php?id=<?php echo (int)$o['id']; ?>">#<?php echo (int)$o['id']; ?></a></td>
        <td><span class="pill"><?php echo e($o['status']); ?></span></td>
        <td><?php echo e(number_format((float)$o['total_amount'], 2)); ?></td>
        <td><?php echo e((string)$o['created_at']); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
<?php layout_footer(); ?>

