<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$u = current_user();
if ($u['role'] !== 'retailer') {
    redirect('/business_store/dashboard.php');
}
?>
<?php layout_header('Retailer - Index'); ?>
<div class="card">
<h1>Retailer Dashboard</h1>
  <p>Welcome, <?php echo e($u['full_name']); ?></p>

  <ul>
    <li><a href="/business_store/retailer/products.php">My Products</a></li>
    <li><a href="/business_store/retailer/orders.php">My Orders</a></li>

  </ul>

  <p><a href="/business_store/logout.php">Logout</a></p>
</div>
<?php layout_footer(); ?>
