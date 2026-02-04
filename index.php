<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (is_logged_in()) {
    $u = current_user();
    redirect(role_home_path($u));
}

redirect('/business_store/home.php');
