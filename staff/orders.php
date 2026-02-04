<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();
$u = current_user();
if ($u['role'] !== 'staff') {
    redirect('/business_store/dashboard.php');
}

$pdo = db();
$status = trim((string)($_GET['status'] ?? ''));

$sql = "SELECT id, status, total_amount, created_at FROM orders";
$params = [];

if ($status !== '') {
    $sql .= " WHERE status = ?";
    $params[] = $status;
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$allStatuses = ['placed','paid','processing','shipped','delivered','cancelled'];
?>
<?php layout_header('Staff - Orders'); ?>
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
      <div style="font-weight:bold;">Orders (Staff)</div>
      <div class="muted"><a href="/business_store/staff/index.php">Back Office Home</a></div>
    </div>
    <div>
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
        <th>Total</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td><a href="/business_store/staff/order.php?id=<?php echo (int)$o['id']; ?>">#<?php echo (int)$o['id']; ?></a></td>
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
