<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$u = current_user();
if ($u['role'] !== 'staff') {
    redirect('/dashboard.php');
}
?>
<?php layout_header('Staff - Index'); ?>
<div class="card">
<h1>Back Office Dashboard</h1>
  <p>Welcome, <?php echo e($u['full_name']); ?></p>
  <p><a href="/logout.php">Logout</a></p>
  <ul>
    <li><a href="/staff/orders.php">Manage Orders</a></li>
  </ul>
</div>
<?php layout_footer(); ?>

