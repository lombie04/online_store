<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

try {
    $pdo = db();
    $stmt = $pdo->query("SELECT 'ok' AS status");
    $row = $stmt->fetch();

    echo "<h1>DB Connection Test</h1>";
    echo "<p>Status: " . htmlspecialchars($row['status']) . "</p>";
    echo "<p>Database: " . htmlspecialchars(DB_NAME) . "</p>";
} catch (Throwable $e) {
    echo "<h1>DB Connection Test</h1>";
    echo "<p>Connection failed.</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
