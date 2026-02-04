<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();
$u = current_user();
if ($u['role'] !== 'admin') redirect('/business_store/dashboard.php');

$pdo = db();
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['product_id'] ?? 0);
    if ($pid <= 0) {
        $error = "Invalid product.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT is_active FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$pid]);
            $p = $stmt->fetch();
            if (!$p) throw new RuntimeException("Product not found.");

            $new = ((int)$p['is_active'] === 1) ? 0 : 1;

            $stmt = $pdo->prepare("UPDATE products SET is_active = ? WHERE id = ?");
            $stmt->execute([$new, $pid]);

            $stmt = $pdo->prepare("
              INSERT INTO audit_logs (actor_user_id, action, entity_type, entity_id, ip_address)
              VALUES (?, ?, 'product', ?, ?)
            ");
            $stmt->execute([
                (int)$u['id'],
                $new ? 'admin_activate_product' : 'admin_deactivate_product',
                $pid,
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);

            $pdo->commit();
            $success = $new ? "Product activated." : "Product deactivated.";
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$stmt = $pdo->query("
  SELECT p.id, p.name, p.price, p.stock, p.is_active, p.created_at,
         c.name AS category_name, r.store_name
  FROM products p
  LEFT JOIN categories c ON c.id = p.category_id
  JOIN retailers r ON r.id = p.retailer_id
  ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();
?>
<?php layout_header('Admin - Products Manage'); ?>
<style>
.wrap{max-width:1100px;margin:40px auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,.08)}
    .top{display:flex;justify-content:space-between;align-items:center}
    a{color:#0b5ed7;text-decoration:none}
    table{width:100%;border-collapse:collapse;margin-top:14px}
    th,td{padding:10px;border-bottom:1px solid #e6e9ee;text-align:left;font-size:14px}
    th{background:#f2f4f7}
    .msg{padding:10px;border-radius:8px;margin-top:12px}
    .ok{background:#e9fff0;color:#135a2e}
    .err{background:#ffe8e8;color:#7a1d1d}
    .pill{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;background:#eef2f7}
    button{padding:8px 10px;border:0;border-radius:8px;cursor:pointer;font-weight:bold;font-size:13px}
    .btnD{background:#ffe8e8;color:#7a1d1d}
    .btnA{background:#e9fff0;color:#135a2e}
    form{display:inline}
</style>
<div class="card">
<div class="wrap">
  <div class="top">
    <h2>Manage Products (Moderation)</h2>
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
        <th>Product</th>
        <th>Store</th>
        <th>Category</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Status</th>
        <th>Created</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p): ?>
      <tr>
        <td><?php echo e($p['name']); ?></td>
        <td><?php echo e($p['store_name']); ?></td>
        <td><?php echo e($p['category_name'] ?? 'Uncategorized'); ?></td>
        <td><?php echo e(number_format((float)$p['price'], 2)); ?></td>
        <td><?php echo (int)$p['stock']; ?></td>
        <td><span class="pill"><?php echo ((int)$p['is_active'] === 1) ? 'active' : 'inactive'; ?></span></td>
        <td><?php echo e((string)$p['created_at']); ?></td>
        <td>
          <form method="post">
            <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
            <?php if ((int)$p['is_active'] === 1): ?>
              <button class="btnD" type="submit">Deactivate</button>
            <?php else: ?>
              <button class="btnA" type="submit">Activate</button>
            <?php endif; ?>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
<?php layout_footer(); ?>
