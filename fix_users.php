<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$pdo = db();

// Add missing column expected by includes/auth.php
$pdo->exec("ALTER TABLE users ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active';");

// Optional: keep consistency if some rows exist
$pdo->exec("UPDATE users SET status = 'active' WHERE status IS NULL OR status = '';");

echo "users table fixed (status column added).";
