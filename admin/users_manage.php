<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();
$u = current_user();
if ($u['role'] !== 'admin') {
    redirect('/business_store/dashboard.php');
}

$pdo = db();
$success = "";
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');

    if ($userId <= 0 || !in_array($action, ['disable','enable'], true)) {
        $error = "Invalid request.";
    } elseif ($userId === (int)$u['id']) {
        $error = "You cannot disable your own admin account.";
    } else {
        try {
            $newStatus = ($action === 'disable') ? 'disabled' : 'active';

            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $userId]);

            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (actor_user_id, action, entity_type, entity_id, ip_address)
                VALUES (?, ?, 'user', ?, ?)
            ");
            $stmt->execute([
                $u['id'],
                ($action === 'disable') ? 'disable_user' : 'enable_user',
                $userId,
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);

            $success = "User status updated to: " . $newStatus;
        } catch (Throwable $e) {
            $error = "Failed: " . $e->getMessage();
        }
    }
}
$stmt = $pdo->query("
    SELECT id, full_name, email, role, status, created_at
    FROM users
    ORDER BY FIELD(role, 'admin','staff','retailer','customer'), created_at DESC
");
$users = $stmt->fetchAll();
?>
<?php layout_header('Admin - Users Manage'); ?>
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
    .btnD{background:#ffe8e8}
    .btnE{background:#e9fff0}
    form{display:inline}
</style>
<div class="card">
<div class="wrap">
  <div class="top">
    <h2>Manage Users</h2>
    <div>
      <a href="/business_store/admin/index.php">Admin Home</a> |
      <a href="/business_store/logout.php">Logout</a>
    </div>
  </div>

  <?php if ($success !== ""): ?><div class="msg ok"><?php echo e($success); ?></div><?php endif; ?>
  <?php if ($error !== ""): ?><div class="msg err"><?php echo e($error); ?></div><?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Created</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $row): ?>
      <tr>
        <td><?php echo e($row['full_name']); ?></td>
        <td><?php echo e($row['email']); ?></td>
        <td><span class="pill"><?php echo e($row['role']); ?></span></td>
        <td><span class="pill"><?php echo e($row['status']); ?></span></td>
        <td><?php echo e((string)$row['created_at']); ?></td>
        <td>
          <?php if ((int)$row['id'] === (int)$u['id']): ?>
            <span class="pill">Current admin</span>
          <?php else: ?>
            <?php if ($row['status'] === 'active'): ?>
              <form method="post">
                <input type="hidden" name="user_id" value="<?php echo (int)$row['id']; ?>">
                <input type="hidden" name="action" value="disable">
                <button class="btn btnD" type="submit">Disable</button>
              </form>
            <?php else: ?>
              <form method="post">
                <input type="hidden" name="user_id" value="<?php echo (int)$row['id']; ?>">
                <input type="hidden" name="action" value="enable">
                <button class="btn btnE" type="submit">Enable</button>
              </form>
            <?php endif; ?>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
<?php layout_footer(); ?>
