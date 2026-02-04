<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/cart.php';

require_login();
$u = current_user();
if ($u['role'] !== 'customer') {
    redirect('/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/customer/index.php');
}

$productId = (int)($_POST['product_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 1);

if ($productId <= 0) {
    redirect('/customer/index.php');
}

$pdo = db();
$stmt = $pdo->prepare("
  SELECT p.id, p.stock, p.is_active, r.approval_status
  FROM products p
  JOIN retailers r ON r.id = p.retailer_id
  WHERE p.id = ?
  LIMIT 1
");
$stmt->execute([$productId]);
$p = $stmt->fetch();

if (!$p) {
    redirect('/customer/index.php');
}

if ((int)$p['is_active'] !== 1 || $p['approval_status'] !== 'approved') {
    redirect('/customer/index.php');
}

if ((int)$p['stock'] <= 0) {
    redirect('/customer/product.php?id=' . $productId);
}

cart_add($productId, max(1, $qty));
redirect('/customer/cart.php');

