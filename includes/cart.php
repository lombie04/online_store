<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function cart_get(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

function cart_set(array $cart): void
{
    $_SESSION['cart'] = $cart;
}

function cart_count_items(): int
{
    $cart = cart_get();
    $sum = 0;
    foreach ($cart as $qty) {
        $sum += (int)$qty;
    }
    return $sum;
}

function cart_add(int $productId, int $qty): void
{
    $qty = max(1, $qty);
    $cart = cart_get();

    if (!isset($cart[$productId])) {
        $cart[$productId] = 0;
    }
    $cart[$productId] += $qty;

    cart_set($cart);
}

function cart_update(int $productId, int $qty): void
{
    $cart = cart_get();
    if ($qty <= 0) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $qty;
    }
    cart_set($cart);
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
}

function cart_fetch_items(PDO $pdo): array
{
    $cart = cart_get();
    if (count($cart) === 0) return [];

    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "
      SELECT
        p.id, p.name, p.price, p.stock, p.image_path,
        p.is_active, r.approval_status, r.store_name
      FROM products p
      JOIN retailers r ON r.id = p.retailer_id
      WHERE p.id IN ($placeholders)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();

    $map = [];
    foreach ($rows as $row) {
        $map[(int)$row['id']] = $row;
    }

    $items = [];
    foreach ($cart as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        if (!isset($map[$pid])) continue;

        $row = $map[$pid];
        $items[] = [
            'product_id' => $pid,
            'name' => $row['name'],
            'price' => (float)$row['price'],
            'stock' => (int)$row['stock'],
            'image_path' => $row['image_path'],
            'is_active' => (int)$row['is_active'],
            'approval_status' => $row['approval_status'],
            'store_name' => $row['store_name'],
            'qty' => $qty
        ];
    }

    return $items;
}

