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
if ($retailerId <= 0) {
    redirect('/retailer/pending.php');
}
$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

$success = "";
$error = "";

function save_image_upload(string $fieldName): ?string
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Image upload failed.");
    }

    $tmp = $_FILES[$fieldName]['tmp_name'];
    $original = (string)$_FILES[$fieldName]['name'];

    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];

    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException("Only jpg, jpeg, png, webp allowed.");
    }

    $targetDir = __DIR__ . '/../uploads';
    // Render runs in a container; ensure uploads exists and is writable.
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new RuntimeException("Uploads folder could not be created.");
        }
    }
    if (!is_writable($targetDir)) {
        throw new RuntimeException("Uploads folder is not writable.");
    }

    $newName = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetPath = $targetDir . '/' . $newName;

    if (!move_uploaded_file($tmp, $targetPath)) {
        throw new RuntimeException("Could not save uploaded image.");
    }
    return 'uploads/' . $newName;
}

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
            $imagePath = save_image_upload('image');

            $stmt = $pdo->prepare("
                INSERT INTO products (retailer_id, category_id, name, description, price, stock, image_path, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $retailerId,
                $categoryId,
                $name,
                $description !== '' ? $description : null,
                (float)$price,
                (int)$stock,
                $imagePath
            ]);

            $success = "Product added successfully.";
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<?php layout_header('Retailer - Product Create'); ?>
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
    <h2>Add Product</h2>
    <div>
      <a href="/retailer/products.php">My Products</a> |
      <a href="/logout.php">Logout</a>
    </div>
  </div>

  <?php if ($success !== ""): ?><div class="msg ok"><?php echo e($success); ?></div><?php endif; ?>
  <?php if ($error !== ""): ?><div class="msg err"><?php echo e($error); ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <label>Name</label>
    <input name="name" required>

    <label>Category</label>
    <select name="category_id">
      <option value="">Uncategorized</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?php echo (int)$c['id']; ?>"><?php echo e($c['name']); ?></option>
      <?php endforeach; ?>
    </select>

    <label>Description</label>
    <textarea name="description" placeholder="Optional"></textarea>

    <label>Price</label>
    <input name="price" type="number" step="0.01" min="0" required>

    <label>Stock</label>
    <input name="stock" type="number" step="1" min="0" value="0" required>

    <label>Image (jpg/jpeg/png/webp)</label>
    <input name="image" type="file" accept=".jpg,.jpeg,.png,.webp">

    <button type="submit">Save</button>
  </form>
</div>
</div>
<?php layout_footer(); ?>

