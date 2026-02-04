<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_approved_retailer();
$u = current_user();

$pdo = db();

$stmt = $pdo->prepare("SELECT id FROM retailers WHERE user_id = ? LIMIT 1");
$stmt->execute([$u['id']]);
$retailer = $stmt->fetch();
$retailerId = $retailer ? (int)$retailer['id'] : 0;

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('/business_store/retailer/products.php');
}
$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$stmt = $pdo->prepare("
    SELECT id, category_id, name, description, price, stock, image_path, is_active
    FROM products
    WHERE id = ? AND retailer_id = ?
    LIMIT 1
");
$stmt->execute([$id, $retailerId]);
$product = $stmt->fetch();

if (!$product) {
    redirect('/business_store/retailer/products.php');
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $price = (string)($_POST['price'] ?? '');
    $stock = (string)($_POST['stock'] ?? '0');
    $categoryIdRaw = (string)($_POST['category_id'] ?? '');
    $categoryId = $categoryIdRaw === '' ? null : (int)$categoryIdRaw;

    if ($name === '' || $price === '') {
        $error = "Name and price are required.";
    } elseif (!is_numeric($price) || (float)$price < 0) {
        $error = "Price must be a valid non-negative number.";
    } elseif (!is_numeric($stock) || (int)$stock < 0) {
        $error = "Stock must be a valid non-negative integer.";
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE products
                SET category_id = ?, name = ?, description = ?, price = ?, stock = ?
                WHERE id = ? AND retailer_id = ?
            ");
            $stmt->execute([
                $categoryId,
                $name,
                $description !== '' ? $description : null,
                (float)$price,
                (int)$stock,
                $id,
                $retailerId
            ]);

            $success = "Product updated successfully.";
            $stmt = $pdo->prepare("
                SELECT id, category_id, name, description, price, stock, image_path, is_active
                FROM products
                WHERE id = ? AND retailer_id = ?
                LIMIT 1
            ");
            $stmt->execute([$id, $retailerId]);
            $product = $stmt->fetch();
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<?php layout_header('Retailer - Product Edit'); ?>
<style>
.wrap{max-width:720px;margin:40px auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,.08)}
    a{color:#0b5ed7;text-decoration:none}
    label{display:block;margin-top:12px}
    input,select,textarea{width:100%;padding:10px;margin-top:6px;border:1px solid #ccd2da;border-radius:8px}
    textarea{min-height:120px;resize:vertical}
    button{margin-top:16px;padding:10px 14px;border:0;border-radius:8px;cursor:pointer;font-weight:bold}
    .msg{padding:10px;border-radius:8px;margin-top:12px}
    .ok{background:#e9fff0;color:#135a2e}
    .err{background:#ffe8e8;color:#7a1d1d}
    .top{display:flex;justify-content:space-between;align-items:center}
</style>
<div class="card">
<div class="wrap">
  <div class="top">
    <h2>Edit Product</h2>
    <div>
      <a href="/business_store/retailer/products.php">My Products</a> |
      <a href="/business_store/logout.php">Logout</a>
    </div>
  </div>

  <?php if ($success !== ""): ?><div class="msg ok"><?php echo e($success); ?></div><?php endif; ?>
  <?php if ($error !== ""): ?><div class="msg err"><?php echo e($error); ?></div><?php endif; ?>

  <form method="post">
    <label>Name</label>
    <input name="name" value="<?php echo e($product['name']); ?>" required>

    <label>Category</label>
    <select name="category_id">
      <option value="">Uncategorized</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)$product['category_id'] === (int)$c['id']) ? 'selected' : ''; ?>>
          <?php echo e($c['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Description</label>
    <textarea name="description"><?php echo e((string)($product['description'] ?? '')); ?></textarea>

    <label>Price</label>
    <input name="price" type="number" step="0.01" min="0" value="<?php echo e((string)$product['price']); ?>" required>

    <label>Stock</label>
    <input name="stock" type="number" step="1" min="0" value="<?php echo e((string)$product['stock']); ?>" required>

    <button type="submit">Save Changes</button>
  </form>
</div>
</div>
<?php layout_footer(); ?>
