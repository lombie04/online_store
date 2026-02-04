<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (is_logged_in()) {
    $u = current_user();
    redirect(role_home_path($u));
}
$msg = trim((string)($_GET['msg'] ?? ''));
$roleHint = trim((string)($_GET['role'] ?? ''));

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    [$ok, $loginMsg] = login_user($email, $password); // don't overwrite $msg
    if ($ok) {
        $u = current_user();
        redirect(role_home_path($u));
    } else {
        $error = $loginMsg;
    }
}
?>
<?php layout_header('Login'); ?>
<style>
.wrap { max-width: 420px; margin: 60px auto; background:#fff; padding: 24px; border-radius: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
    h1 { margin: 0 0 12px; font-size: 22px; }
    label { display:block; margin-top: 12px; font-size: 14px; }
    input { width:100%; padding: 10px; margin-top: 6px; border:1px solid #ccd2da; border-radius: 8px; }
    button { width:100%; margin-top: 16px; padding: 10px; border:0; border-radius: 8px; cursor:pointer; font-weight: bold; }
    .err { background:#ffe8e8; padding: 10px; border-radius: 8px; margin-top: 12px; color:#7a1d1d; }
    .hint { margin-top: 12px; font-size: 13px; color:#555; }
</style>
<div class="card">
<div class="wrap">
    <h1>Login</h1>

    <?php if ($msg !== ""): ?>
      <div class="err" style="background:#eef2ff;color:#1f2a6b;"><?php echo e($msg); ?></div>
    <?php endif; ?>

    <div class="hint">
      <?php if ($roleHint !== ""): ?>
        You selected: <?php echo e($roleHint); ?> login.
      <?php else: ?>
        Select your role from the home page, or login normally.
      <?php endif; ?>
    </div>

    <div class="hint">
      <a href="/business_store/register.php">Create Customer Account</a> |
      <a href="/business_store/retailer_apply.php">Apply as Retailer</a> |
      <a href="/business_store/home.php">Home</a>
    </div>

    <?php if ($error !== ""): ?>
      <div class="err"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <label>Email</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <button type="submit">Sign in</button>
    </form>

    <div class="hint">
      Use the admin email/password you set in setup_admin.php.
    </div>
  </div>
</div>
<?php layout_footer(); ?>
