<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();
$u = current_user();
if ($u['role'] !== 'customer') {
    redirect('/dashboard.php');
}

$pdo = db();

$q = trim((string)($_GET['q'] ?? ''));
$cat = (int)($_GET['cat'] ?? 0);
$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

$sql = "
  SELECT p.id, p.name, p.price, p.stock, p.image_path, c.name AS category_name, r.store_name
  FROM products p
  LEFT JOIN categories c ON c.id = p.category_id
  JOIN retailers r ON r.id = p.retailer_id
  WHERE p.is_active = 1
    AND r.approval_status = 'approved'
";
$params = [];

if ($cat > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $cat;
}
if ($q !== '') {
    $sql .= " AND p.name LIKE ?";
    $params[] = '%' . $q . '%';
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<?php layout_header('Customer - Index'); ?>
<style>
@media(max-width:1000px){ .wrap > div:last-of-type{ grid-template-columns:repeat(2,1fr) !important; } }
@media(max-width:520px){ .wrap > div:last-of-type{ grid-template-columns:1fr !important; } }
</style>
<!doctype html>
<html lang="en">
<div class="card">
  <h2 style="margin:0;">Storefront</h2>
  <div class="muted">Logged in as <?php echo e($u['full_name']); ?></div>

  <form method="get" action="" style="display:flex;gap:10px;align-items:center;margin-top:14px;flex-wrap:wrap;">
    <input name="q" value="<?php echo e($q); ?>" placeholder="Search products" style="padding:10px;border:1px solid #ccd2da;border-radius:8px;">
    <select name="cat" style="padding:10px;border:1px solid #ccd2da;border-radius:8px;">
      <option value="0">All categories</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?php echo (int)$c['id']; ?>" <?php echo ($cat === (int)$c['id']) ? 'selected' : ''; ?>>
          <?php echo e($c['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" style="padding:10px 14px;border:0;border-radius:8px;cursor:pointer;font-weight:bold;">Filter</button>
  </form>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:14px;">
  <?php foreach ($products as $p): ?>
    <div class="card" style="padding:0;overflow:hidden;">
      <div style="height:150px;background:#eef2f7;display:flex;align-items:center;justify-content:center;">
        <?php if (!empty($p['image_path'])): ?>
          <img src="/<?php echo e($p['image_path']); ?>" alt="" style="max-width:100%;max-height:100%;display:block;">
        <?php else: ?>
          <div class="muted">No image</div>
        <?php endif; ?>
      </div>
      <div style="padding:12px;">
        <div style="font-weight:bold;">
          <a href="/customer/product.php?id=<?php echo (int)$p['id']; ?>">
            <?php echo e($p['name']); ?>
          </a>
        </div>
        <div class="muted"><?php echo e($p['category_name'] ?? 'Uncategorized'); ?> · <?php echo e($p['store_name']); ?></div>
        <div style="margin-top:10px;font-weight:bold;"><?php echo e(number_format((float)$p['price'], 2)); ?></div>
        <div class="muted">Stock: <?php echo e((string)$p['stock']); ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<style>
@media(max-width:1000px){ .wrap > div:last-of-type{ grid-template-columns:repeat(2,1fr) !important; } }
@media(max-width:520px){ .wrap > div:last-of-type{ grid-template-columns:1fr !important; } }
</style>
<?php layout_footer(); ?>

