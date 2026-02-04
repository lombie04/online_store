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
if ($retailerId <= 0) redirect('/business_store/retailer/pending.php');

$status = trim((string)($_GET['status'] ?? ''));

$sql = "
  SELECT DISTINCT o.id, o.status, o.total_amount, o.created_at
  FROM orders o
  JOIN order_items oi ON oi.order_id = o.id
  WHERE oi.retailer_id = ?
";
$params = [$retailerId];

if ($status !== '') {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}
$sql .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$allStatuses = ['placed','paid','processing','shipped','delivered','cancelled'];
?>
<?php layout_header('Retailer - Orders'); ?>
<style>
.wrap{max-width:1100px;margin:30px auto;padding:0 16px}
    .bar{background:#fff;padding:14px 16px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,.08);display:flex;justify-content:space-between;align-items:center}
    a{color:#0b5ed7;text-decoration:none}
    table{width:100%;border-collapse:collapse;background:#fff;margin-top:14px;border-radius:10px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,.08)}
    th,td{padding:10px;border-bottom:1px solid #e6e9ee;text-align:left;font-size:14px}
    th{background:#f2f4f7}
    .pill{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;background:#eef2f7}
    .muted{color:#666;font-size:13px}
    select,button{padding:10px;border-radius:8px;border:1px solid #ccd2da}
    button{border:0;cursor:pointer;font-weight:bold}
    form{display:flex;gap:10px;align-items:center;margin-top:14px}
</style>
<div class="card">
<div class="wrap">
  <div class="bar">
    <div>
      <div style="font-weight:bold;">Orders (Retailer)</div>
      <div class="muted">Store: <?php echo e($retailer['store_name']); ?></div>
    </div>
    <div>
      <a href="/business_store/retailer/index.php">Retailer Home</a> |
      <a href="/business_store/logout.php">Logout</a>
    </div>
  </div>

  <form method="get">
    <select name="status">
      <option value="">All statuses</option>
      <?php foreach ($allStatuses as $s): ?>
        <option value="<?php echo e($s); ?>" <?php echo ($status === $s) ? 'selected' : ''; ?>><?php echo e($s); ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Filter</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>Order</th>
        <th>Status</th>
        <th>Total (whole order)</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td><a href="/business_store/retailer/order.php?id=<?php echo (int)$o['id']; ?>">#<?php echo (int)$o['id']; ?></a></td>
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
