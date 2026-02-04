<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();
$u = current_user();
if ($u['role'] !== 'admin') {
    redirect('/business_store/dashboard.php');
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim((string)($_POST['full_name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role = (string)($_POST['role'] ?? 'customer');

    if ($full_name === "" || $email === "" || $password === "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!in_array($role, ['customer','retailer','staff','admin'], true)) {
        $error = "Invalid role.";
    } else {
        try {
            $pdo = db();

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email already exists.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (full_name, email, password_hash, role, status)
                    VALUES (?, ?, ?, ?, 'active')
                ");
                $stmt->execute([$full_name, $email, $hash, $role]);

                $newUserId = (int)$pdo->lastInsertId();
                if ($role === 'retailer') {
                    $store_name = trim((string)($_POST['store_name'] ?? ''));
                    if ($store_name === '') {
                        $store_name = $full_name . " Store";
                    }
                    $stmt = $pdo->prepare("
                        INSERT INTO retailers (user_id, store_name, approval_status)
                        VALUES (?, ?, 'pending')
                    ");
                    $stmt->execute([$newUserId, $store_name]);
                }

                $success = "User created successfully.";
            }
        } catch (Throwable $e) {
            $error = "Failed: " . $e->getMessage();
        }
    }
}
?>
<?php layout_header('Admin - Users Create'); ?>
<style>
.wrap{max-width:720px;margin:40px auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,.08)}
    a{color:#0b5ed7;text-decoration:none}
    label{display:block;margin-top:12px}
    input,select{width:100%;padding:10px;margin-top:6px;border:1px solid #ccd2da;border-radius:8px}
    button{margin-top:16px;padding:10px 14px;border:0;border-radius:8px;cursor:pointer;font-weight:bold}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .msg{padding:10px;border-radius:8px;margin-top:12px}
    .ok{background:#e9fff0;color:#135a2e}
    .err{background:#ffe8e8;color:#7a1d1d}
    .top{display:flex;justify-content:space-between;align-items:center}
</style>
<div class="card">
<div class="wrap">
  <div class="top">
    <h2>Create User</h2>
    <div>
      <a href="/business_store/admin/index.php">Admin Home</a> |
      <a href="/business_store/logout.php">Logout</a>
    </div>
  </div>

  <?php if ($success !== ""): ?><div class="msg ok"><?php echo e($success); ?></div><?php endif; ?>
  <?php if ($error !== ""): ?><div class="msg err"><?php echo e($error); ?></div><?php endif; ?>

  <form method="post">
    <div class="row">
      <div>
        <label>Full name</label>
        <input name="full_name" required>
      </div>
      <div>
        <label>Email</label>
        <input name="email" type="email" required>
      </div>
    </div>

    <div class="row">
      <div>
        <label>Password</label>
        <input name="password" type="password" required>
      </div>
      <div>
        <label>Role</label>
        <select name="role" id="roleSelect" required>
          <option value="customer">Customer</option>
          <option value="retailer">Retailer</option>
          <option value="staff">Staff (OBS)</option>
          <option value="admin">Admin</option>
        </select>
      </div>
    </div>

    <div id="storeNameBox" style="display:none;">
      <label>Store name (Retailer only)</label>
      <input name="store_name" placeholder="e.g., Lombie Tech Store">
    </div>

    <button type="submit">Create</button>
  </form>

  <script>
    const roleSelect = document.getElementById('roleSelect');
    const storeNameBox = document.getElementById('storeNameBox');

    function toggleStoreName(){
      storeNameBox.style.display = (roleSelect.value === 'retailer') ? 'block' : 'none';
    }
    roleSelect.addEventListener('change', toggleStoreName);
    toggleStoreName();
  </script>
</div>
</div>
<?php layout_footer(); ?>
