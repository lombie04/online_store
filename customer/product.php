<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();
$u = current_user();
if ($u['role'] !== 'customer') {
    redirect('/business_store/dashboard.php');
}

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
  SELECT p.id, p.name, p.description, p.price, p.stock, p.image_path, c.name AS category_name, r.store_name
  FROM products p
  LEFT JOIN categories c ON c.id = p.category_id
  JOIN retailers r ON r.id = p.retailer_id
  WHERE p.id = ? AND p.is_active = 1 AND r.approval_status = 'approved'
  LIMIT 1
");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    redirect('/business_store/customer/index.php');
}
?>
<?php layout_header('Customer - Product'); ?>
<style>
.wrap{max-width:900px;margin:30px auto;padding:0 16px}
    .card{background:#fff;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,.08);overflow:hidden}
    .img{height:320px;background:#eef2f7;display:flex;align-items:center;justify-content:center}
    .img img{max-width:100%;max-height:100%;display:block}
    .p{padding:16px}
    .muted{color:#666;font-size:13px;margin-top:6px}
    .price{margin-top:10px;font-weight:bold;font-size:18px}
    a{color:#0b5ed7;text-decoration:none}
</style>
<div class="wrap">
  <p><a href="/business_store/customer/index.php">Back to Storefront</a></p>

  <div class="card">
    <div class="img">
      <?php if (!empty($p['image_path'])): ?>
        <img src="/business_store/<?php echo e($p['image_path']); ?>" alt="">
      <?php else: ?>
        <div class="muted">No image</div>
      <?php endif; ?>
    </div>
    <div class="p">
      <h2 style="margin:0;"><?php echo e($p['name']); ?></h2>
      <div class="muted"><?php echo e($p['category_name'] ?? 'Uncategorized'); ?> · <?php echo e($p['store_name']); ?></div>

      <div class="price"><?php echo e(number_format((float)$p['price'], 2)); ?></div>
      <div class="muted">Stock: <?php echo e((string)$p['stock']); ?></div>
        <?php if ((int)$p['stock'] > 0): ?>
            <form method="post" action="/business_store/customer/add_to_cart.php" style="margin-top:12px;">
                <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                <label style="display:block;margin-bottom:6px;">Quantity</label>
                <input type="number" name="qty" min="1" max="<?php echo (int)$p['stock']; ?>" value="1" style="padding:10px;border:1px solid #ccd2da;border-radius:8px;width:120px;">
                <button type="submit" style="padding:10px 14px;border:0;border-radius:8px;cursor:pointer;font-weight:bold;margin-left:8px;">Add to cart</button>
            </form>
        <?php else: ?>
            <p class="muted" style="margin-top:12px;">Out of stock</p>
        <?php endif; ?>
      <p><?php echo nl2br(e((string)($p['description'] ?? ''))); ?></p>
    </div>
  </div>
</div>
<?php layout_footer(); ?>
