<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();
$u = current_user();
if ($u['role'] !== 'admin') {
    redirect('/dashboard.php');
}

$pdo = db();
$success = "";
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $retailerId = (int)($_POST['retailer_id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');

    if ($retailerId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
        $error = "Invalid request.";
    } else {
        try {
            $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

            $stmt = $pdo->prepare("UPDATE retailers SET approval_status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $retailerId]);
            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (actor_user_id, action, entity_type, entity_id, ip_address)
                VALUES (?, ?, 'retailer', ?, ?)
            ");
            $stmt->execute([
                $u['id'],
                ($action === 'approve') ? 'approve_retailer' : 'reject_retailer',
                $retailerId,
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);

            $success = "Retailer status updated to: " . $newStatus;
        } catch (Throwable $e) {
            $error = "Failed: " . $e->getMessage();
        }
    }
}
$stmt = $pdo->query("
    SELECT r.id AS retailer_id, r.store_name, r.approval_status, r.created_at,
           u.id AS user_id, u.full_name, u.email, u.status AS user_status
    FROM retailers r
    JOIN users u ON u.id = r.user_id
    ORDER BY
      FIELD(r.approval_status, 'pending','approved','rejected'),
      r.created_at DESC
");
$retailers = $stmt->fetchAll();
?>
<?php layout_header('Admin - Retailers Approve'); ?>
<style>
.wrap{max-width:1000px;margin:40px auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,.08)}
    .top{display:flex;justify-content:space-between;align-items:center}
    a{color:#0b5ed7;text-decoration:none}
    table{width:100%;border-collapse:collapse;margin-top:14px}
    th,td{padding:10px;border-bottom:1px solid #e6e9ee;text-align:left;font-size:14px}
    th{background:#f2f4f7}
    .msg{padding:10px;border-radius:8px;margin-top:12px}
    .ok{background:#e9fff0;color:#135a2e}
    .err{background:#ffe8e8;color:#7a1d1d}
    .pill{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;background:#eef2f7}
    .btn{padding:8px 10px;border:0;border-radius:8px;cursor:pointer;font-weight:bold;font-size:13px}
    .btnA{background:#e9fff0}
    .btnR{background:#ffe8e8}
    .muted{color:#666;font-size:13px}
    form{display:inline}
</style>
<div class="card">
<div class="wrap">
  <div class="top">
    <h2>Approve Retailers</h2>
    <div>
      <a href="/admin/index.php">Admin Home</a> |
      <a href="/logout.php">Logout</a>
    </div>
  </div>

  <p class="muted">Pending retailers cannot access the retailer dashboard until approved.</p>

  <?php if ($success !== ""): ?><div class="msg ok"><?php echo e($success); ?></div><?php endif; ?>
  <?php if ($error !== ""): ?><div class="msg err"><?php echo e($error); ?></div><?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>Store</th>
        <th>Retailer User</th>
        <th>Email</th>
        <th>User Status</th>
        <th>Approval</th>
        <th>Created</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($retailers as $r): ?>
      <tr>
        <td><?php echo e($r['store_name']); ?></td>
        <td><?php echo e($r['full_name']); ?></td>
        <td><?php echo e($r['email']); ?></td>
        <td><span class="pill"><?php echo e($r['user_status']); ?></span></td>
        <td><span class="pill"><?php echo e($r['approval_status']); ?></span></td>
        <td><?php echo e((string)$r['created_at']); ?></td>
        <td>
          <?php if ($r['approval_status'] === 'pending'): ?>
            <form method="post">
              <input type="hidden" name="retailer_id" value="<?php echo (int)$r['retailer_id']; ?>">
              <input type="hidden" name="action" value="approve">
              <button class="btn btnA" type="submit">Approve</button>
            </form>
            <form method="post">
              <input type="hidden" name="retailer_id" value="<?php echo (int)$r['retailer_id']; ?>">
              <input type="hidden" name="action" value="reject">
              <button class="btn btnR" type="submit">Reject</button>
            </form>
          <?php else: ?>
            <span class="muted">No action</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
<?php layout_footer(); ?>

