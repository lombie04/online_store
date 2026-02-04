<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_approved_retailer();
$u = current_user();

$pdo = db();

// Get retailer_id for this user
$stmt = $pdo->prepare("SELECT id, store_name FROM retailers WHERE user_id = ? LIMIT 1");
$stmt->execute([$u['id']]);
$retailer = $stmt->fetch();

if (!$retailer) {
    redirect('/retailer/pending.php');
}

$retailerId = (int)$retailer['id'];

// Load retailer products
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, p.stock, p.is_active, c.name AS category_name, p.created_at
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.retailer_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$retailerId]);
$products = $stmt->fetchAll();
?>
<?php layout_header('Retailer - Products'); ?>
<style>
.wrap{max-width:1100px;margin:40px auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,.08)}
    .top{display:flex;justify-content:space-between;align-items:center}
    a{color:#0b5ed7;text-decoration:none}
    table{width:100%;border-collapse:collapse;margin-top:14px}
    th,td{padding:10px;border-bottom:1px solid #e6e9ee;text-align:left;font-size:14px}
    th{background:#f2f4f7}
    .pill{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;background:#eef2f7}
    .btn{display:inline-block;padding:8px 10px;border-radius:8px;font-weight:bold;font-size:13px;text-decoration:none}
    .btnAdd{background:#e9fff0;color:#135a2e}
    .btnEdit{background:#eef2ff;color:#1f2a6b}
    .btnToggle{background:#ffe8e8;color:#7a1d1d}
    .muted{color:#666;font-size:13px}
</style>
<div class="card">
<div class="wrap">
  <div class="top">
    <div>
      <h2>My Products</h2>
      <div class="muted">Store: <?php echo e($retailer['store_name']); ?></div>
    </div>
    <div>
      <a href="/retailer/index.php">Retailer Home</a> |
      <a href="/logout.php">Logout</a>
    </div>
  </div>

  <p>
    <a class="btn btnAdd" href="/retailer/product_create.php">Add New Product</a>
  </p>

  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Category</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Status</th>
        <th>Created</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p): ?>
      <tr>
        <td><?php echo e($p['name']); ?></td>
        <td><?php echo e($p['category_name'] ?? 'Uncategorized'); ?></td>
        <td><?php echo e(number_format((float)$p['price'], 2)); ?></td>
        <td><?php echo e((string)$p['stock']); ?></td>
        <td><span class="pill"><?php echo $p['is_active'] ? 'active' : 'inactive'; ?></span></td>
        <td><?php echo e((string)$p['created_at']); ?></td>
        <td>
          <a class="btn btnEdit" href="/retailer/product_edit.php?id=<?php echo (int)$p['id']; ?>">Edit</a>
          <a class="btn btnToggle" href="/retailer/product_toggle.php?id=<?php echo (int)$p['id']; ?>">
            <?php echo $p['is_active'] ? 'Deactivate' : 'Activate'; ?>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
<?php layout_footer(); ?>

