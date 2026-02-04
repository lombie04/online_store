<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout.php';
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

