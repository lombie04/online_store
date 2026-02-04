<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$u = current_user();
if ($u['role'] !== 'retailer') {
    redirect('/dashboard.php');
}
?>
<?php layout_header('Retailer - Pending'); ?>
<div class="card">
<h1>Retailer Access Pending</h1>
  <p>Your retailer account is not approved yet.</p>
  <p><a href="/logout.php">Logout</a></p>
</div>
<?php layout_footer(); ?>

