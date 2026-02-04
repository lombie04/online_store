<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    $u = current_user();
    redirect(role_home_path($u));
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim((string)($_POST['full_name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password2'] ?? '');

    if ($full_name === "" || $email === "" || $password === "" || $password2 === "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $password2) {
        $error = "Passwords do not match.";
    } else {
        try {
            $pdo = db();

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email already exists. Please login.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                  INSERT INTO users (full_name, email, password_hash, role, status)
                  VALUES (?, ?, ?, 'customer', 'active')
                ");
                $stmt->execute([$full_name, $email, $hash]);

                redirect('/login.php?msg=' . urlencode('Account created. Please login.'));
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<?php layout_header('Create Account'); ?>
<style>
.wrap{max-width:520px;margin:60px auto;background:#fff;padding:24px;border-radius:12px;box-shadow:0 8px 20px rgba(0,0,0,.08)}
    a{color:#0b5ed7;text-decoration:none}
    label{display:block;margin-top:12px;font-size:14px}
    input{width:100%;padding:10px;margin-top:6px;border:1px solid #ccd2da;border-radius:8px}
    button{width:100%;margin-top:16px;padding:10px;border:0;border-radius:8px;cursor:pointer;font-weight:bold}
    .msg{padding:10px;border-radius:8px;margin-top:12px}
    .err{background:#ffe8e8;color:#7a1d1d}
    .muted{color:#666;font-size:13px;margin-top:10px}
</style>
<div class="card">
<div class="wrap">
    <h2 style="margin:0;">Create Customer Account</h2>
    <div class="muted">Register to shop and track your orders.</div>

    <?php if ($error !== ""): ?><div class="msg err"><?php echo e($error); ?></div><?php endif; ?>

    <form method="post">
      <label>Full name</label>
      <input name="full_name" required>

      <label>Email</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <label>Confirm password</label>
      <input type="password" name="password2" required>

      <button type="submit">Create account</button>
    </form>

    <p class="muted">
      Already have an account? <a href="/login.php?role=customer">Login</a>
    </p>
    <p class="muted">
      <a href="/home.php">Back to Home</a>
    </p>
  </div>
</div>
<?php layout_footer(); ?>

