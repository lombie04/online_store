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
    redirect('/retailer/products.php');
}

$stmt = $pdo->prepare("SELECT is_active FROM products WHERE id = ? AND retailer_id = ? LIMIT 1");
$stmt->execute([$id, $retailerId]);
$p = $stmt->fetch();

if (!$p) {
    redirect('/retailer/products.php');
}

$new = $p['is_active'] ? 0 : 1;

$stmt = $pdo->prepare("UPDATE products SET is_active = ? WHERE id = ? AND retailer_id = ?");
$stmt->execute([$new, $id, $retailerId]);

redirect('/retailer/products.php');

