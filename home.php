<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (is_logged_in()) {
    $u = current_user();
    redirect(role_home_path($u));
}
?>
<?php layout_header('Home'); ?>
<style>
.wrap{max-width:900px;margin:60px auto;padding:0 16px}
    .card{background:#fff;padding:24px;border-radius:12px;box-shadow:0 8px 20px rgba(0,0,0,.08)}
    h1{margin:0 0 8px}
    .muted{color:#666}
    .grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:16px}
    a.btn{display:block;text-decoration:none;padding:14px;border-radius:10px;font-weight:bold;text-align:center;background:#eef2f7;color:#111}
    a.btn:hover{opacity:.9}
    .row{display:flex;gap:12px;flex-wrap:wrap;margin-top:16px}
    a.link{color:#0b5ed7;text-decoration:none;font-weight:bold}
    @media(max-width:520px){.grid{grid-template-columns:1fr}}
</style>
<div class="wrap">
  <div class="card">
    <h1>Business Store</h1>
    <div class="muted">Choose how you want to use the platform.</div>

    <div class="grid">
      <a class="btn" href="/login.php?role=customer">Customer Login</a>
      <a class="btn" href="/login.php?role=retailer">Retailer Login</a>
      <a class="btn" href="/login.php?role=staff">Back Office (Staff) Login</a>
      <a class="btn" href="/login.php?role=admin">Admin Login</a>
    </div>

    <div class="row">
      <a class="link" href="/register.php">Create Customer Account</a>
      <a class="link" href="/retailer_apply.php">Apply as Retailer</a>
    </div>
  </div>
</div>
<?php layout_footer(); ?>

