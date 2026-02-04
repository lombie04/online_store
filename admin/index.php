<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/layout.php';

require_login();
$u = current_user();
if ($u['role'] !== 'admin') {
    redirect('/business_store/dashboard.php');
}

layout_header('Admin Dashboard');
?>

<div class="card">
  <h2 style="margin:0;">Admin Dashboard</h2>
  <div class="muted">Welcome, <?php echo e($u['full_name']); ?></div>
</div>

<div class="grid menu" style="margin-top:14px;">
  <a class="tile" href="/business_store/admin/users_create.php">
    <div class="tile-body">
      <div class="tile-title">Create Users</div>
      <div class="muted">Add staff, retailers, or customers.</div>
    </div>
  </a>

  <a class="tile" href="/business_store/admin/users_manage.php">
    <div class="tile-body">
      <div class="tile-title">Manage Users</div>
      <div class="muted">Enable/disable accounts.</div>
    </div>
  </a>

  <a class="tile" href="/business_store/admin/retailers_approve.php">
    <div class="tile-body">
      <div class="tile-title">Approve Retailers</div>
      <div class="muted">Approve or reject applications.</div>
    </div>
  </a>

  <a class="tile" href="/business_store/admin/products_manage.php">
    <div class="tile-body">
      <div class="tile-title">Moderate Products</div>
      <div class="muted">Deactivate/activate any listing.</div>
    </div>
  </a>
</div>

<?php layout_footer(); ?>
