<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/*
  Protected schema synchronizer for Render/Aiven.
  - Set MIGRATE_KEY in Render env vars
  - Run: /schema_sync.php?key=YOUR_KEY
*/

$key   = getenv('MIGRATE_KEY') ?: '';
$given = $_GET['key'] ?? '';

if ($key === '' || !hash_equals($key, $given)) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

$pdo = db();
$dbName = (string)$pdo->query("SELECT DATABASE()")->fetchColumn();

function table_exists(PDO $pdo, string $dbName, string $table): bool {
    $st = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
    $st->execute([$dbName, $table]);
    return (int)$st->fetchColumn() > 0;
}

function column_exists(PDO $pdo, string $dbName, string $table, string $column): bool {
    $st = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?");
    $st->execute([$dbName, $table, $column]);
    return (int)$st->fetchColumn() > 0;
}

function add_column_if_missing(PDO $pdo, string $dbName, string $table, string $column, string $ddl): void {
    if (!column_exists($pdo, $dbName, $table, $column)) {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN {$ddl}");
    }
}

/* =========================
   USERS
   ========================= */
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(30) NOT NULL DEFAULT 'customer',
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");

add_column_if_missing($pdo, $dbName, 'users', 'full_name',      "full_name VARCHAR(120) NOT NULL DEFAULT ''");
add_column_if_missing($pdo, $dbName, 'users', 'email',          "email VARCHAR(190) NOT NULL");
add_column_if_missing($pdo, $dbName, 'users', 'password_hash',  "password_hash VARCHAR(255) NOT NULL DEFAULT ''");
add_column_if_missing($pdo, $dbName, 'users', 'role',           "role VARCHAR(30) NOT NULL DEFAULT 'customer'");
add_column_if_missing($pdo, $dbName, 'users', 'status',         "status VARCHAR(20) NOT NULL DEFAULT 'active'");
add_column_if_missing($pdo, $dbName, 'users', 'is_active',      "is_active TINYINT(1) NOT NULL DEFAULT 1");

/* =========================
   RETAILERS
   ========================= */
$pdo->exec("
CREATE TABLE IF NOT EXISTS retailers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  store_name VARCHAR(190) NOT NULL,
  phone VARCHAR(60) NULL,
  address VARCHAR(255) NULL,
  approval_status VARCHAR(20) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_retailers_user (user_id),
  CONSTRAINT fk_retailers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

add_column_if_missing($pdo, $dbName, 'retailers', 'store_name',      "store_name VARCHAR(190) NOT NULL DEFAULT 'My Store'");
add_column_if_missing($pdo, $dbName, 'retailers', 'phone',           "phone VARCHAR(60) NULL");
add_column_if_missing($pdo, $dbName, 'retailers', 'address',         "address VARCHAR(255) NULL");
add_column_if_missing($pdo, $dbName, 'retailers', 'approval_status', "approval_status VARCHAR(20) NOT NULL DEFAULT 'pending'");

/* Ensure every retailer user has a retailers row */
$pdo->exec("
INSERT INTO retailers (user_id, store_name, approval_status)
SELECT u.id, CONCAT('Store ', u.id), 'pending'
FROM users u
LEFT JOIN retailers r ON r.user_id = u.id
WHERE u.role = 'retailer' AND r.user_id IS NULL;
");

/* =========================
   CATEGORIES
   ========================= */
$pdo->exec("
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB;
");

$pdo->exec("
INSERT IGNORE INTO categories (name) VALUES
('General'), ('Electronics'), ('Fashion'), ('Home'), ('Food');
");

/* =========================
   PRODUCTS
   ========================= */
$pdo->exec("
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  retailer_id INT NOT NULL,
  category_id INT NULL,
  name VARCHAR(190) NOT NULL,
  description TEXT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  image_path VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_products_retailer (retailer_id),
  INDEX idx_products_category (category_id),
  CONSTRAINT fk_products_retailer FOREIGN KEY (retailer_id) REFERENCES retailers(id) ON DELETE CASCADE,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;
");

add_column_if_missing($pdo, $dbName, 'products', 'description', "description TEXT NULL");
add_column_if_missing($pdo, $dbName, 'products', 'is_active',   "is_active TINYINT(1) NOT NULL DEFAULT 1");
add_column_if_missing($pdo, $dbName, 'products', 'image_path',  "image_path VARCHAR(255) NULL");

/* =========================
   ORDERS
   ========================= */
$pdo->exec("
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_orders_customer (customer_id),
  INDEX idx_orders_created (created_at),
  CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

add_column_if_missing($pdo, $dbName, 'orders', 'customer_id',   "customer_id INT NOT NULL");
add_column_if_missing($pdo, $dbName, 'orders', 'total_amount',  "total_amount DECIMAL(10,2) NOT NULL DEFAULT 0");
add_column_if_missing($pdo, $dbName, 'orders', 'status',        "status VARCHAR(30) NOT NULL DEFAULT 'pending'");

/* =========================
   ORDER ITEMS
   ========================= */
$pdo->exec("
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  retailer_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  line_total DECIMAL(10,2) NOT NULL DEFAULT 0,
  INDEX idx_items_order (order_id),
  INDEX idx_items_product (product_id),
  INDEX idx_items_retailer (retailer_id),
  CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
  CONSTRAINT fk_items_retailer FOREIGN KEY (retailer_id) REFERENCES retailers(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

add_column_if_missing($pdo, $dbName, 'order_items', 'quantity',   "quantity INT NOT NULL DEFAULT 1");
add_column_if_missing($pdo, $dbName, 'order_items', 'unit_price', "unit_price DECIMAL(10,2) NOT NULL DEFAULT 0");
add_column_if_missing($pdo, $dbName, 'order_items', 'line_total', "line_total DECIMAL(10,2) NOT NULL DEFAULT 0");

/* =========================
   ORDER STATUS HISTORY
   ========================= */
$pdo->exec("
CREATE TABLE IF NOT EXISTS order_status_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  old_status VARCHAR(30) NULL,
  new_status VARCHAR(30) NOT NULL,
  changed_by_user_id INT NULL,
  changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_osh_order (order_id),
  INDEX idx_osh_changed (changed_at),
  CONSTRAINT fk_osh_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_osh_user FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
");

/* =========================
   AUDIT LOGS
   ========================= */
$pdo->exec("
CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  actor_user_id INT NULL,
  action VARCHAR(120) NOT NULL,
  entity_type VARCHAR(60) NOT NULL,
  entity_id INT NULL,
  ip_address VARCHAR(64) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_actor (actor_user_id),
  INDEX idx_audit_created (created_at),
  CONSTRAINT fk_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
");

echo "Schema sync OK";
