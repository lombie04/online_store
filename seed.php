<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$pdo = db();

$users = [
  ['Admin',    'admin@example.com',     'Admin123!',    'admin'],
  ['Customer', 'customer1@gmail.com',   'Lombie1!',     'customer'],
  ['Staff',    'staff@example.com',     'Staff123!',    'staff'],
  ['Retailer', 'retailer@example.com',  'Retailer123!', 'retailer'],
];

$stmt = $pdo->prepare("
  INSERT INTO users (full_name, email, password_hash, role, is_active)
  VALUES (?, ?, ?, ?, 1)
  ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    role = VALUES(role),
    is_active = 1
");

foreach ($users as [$name, $email, $pass, $role]) {
  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $stmt->execute([$name, $email, $hash, $role]);
}

echo "Seeded users successfully.";
