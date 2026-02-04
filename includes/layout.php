<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../db.php';

function layout_header(string $title): void
{
    $u = is_logged_in() ? current_user() : null;
    $role = $u['role'] ?? null;

    $brand = defined('APP_BRAND') ? APP_BRAND : 'Business Store';
    $logo  = defined('APP_LOGO')  ? APP_LOGO  : '';

    echo '<!doctype html><html lang="en"><head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . '</title>';

    echo '<style>
:root{
  --bg1:#0b1220;
  --bg2:#0f766e;
  --card:rgba(255,255,255,0.94);
  --card2:#ffffff;
  --text:#0f172a;
  --muted:#64748b;
  --primary:#2563eb;
  --primary2:#1d4ed8;
  --danger:#dc2626;
  --success:#16a34a;
  --warning:#d97706;
  --ring:rgba(37,99,235,.25);
  --shadow:0 18px 60px rgba(2,6,23,.35);
  --radius:16px;
  --radius2:12px;
  --max:1100px;
  --font: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, "Apple Color Emoji","Segoe UI Emoji";
}

*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0;
  font-family:var(--font);
  color:var(--text);
  background:
    radial-gradient(1200px 600px at 20% 10%, rgba(37,99,235,.25), transparent 60%),
    radial-gradient(1200px 600px at 80% 20%, rgba(15,118,110,.25), transparent 60%),
    linear-gradient(135deg, var(--bg1), #081226 55%, var(--bg2));
}

a{color:var(--primary); text-decoration:none}
a:hover{text-decoration:underline}

.container{
  width: min(var(--max), calc(100% - 48px));
  margin: 32px auto 56px;
}

.topbar{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
  padding:16px 18px;
  border-radius: var(--radius);
  background: rgba(255,255,255,.10);
  backdrop-filter: blur(10px);
  box-shadow: 0 10px 40px rgba(2,6,23,.35);
  border: 1px solid rgba(255,255,255,.14);
}

.brand{
  display:flex;
  align-items:center;
  gap:12px;
  font-weight:800;
  letter-spacing:.2px;
  color:#fff;
}
.brand img{
  width:34px;height:34px;object-fit:contain;border-radius:10px;
  background: rgba(255,255,255,.16);
  padding:6px;
}

.nav{
  display:flex; gap:10px; flex-wrap:wrap;
}
.nav a{
  display:inline-flex;
  align-items:center;
  padding:10px 12px;
  border-radius: 12px;
  color: rgba(255,255,255,.88);
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.12);
  font-weight:600;
}
.nav a:hover{background: rgba(255,255,255,.14); text-decoration:none}
.nav a.active{background: rgba(37,99,235,.35); border-color: rgba(37,99,235,.55)}

.card{
  margin-top:22px;
  background: var(--card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  border: 1px solid rgba(15,23,42,.08);
  overflow:hidden;
}

.card .card-inner{
  padding: 22px;
}

h1,h2,h3{margin:0 0 14px}
h1{font-size:28px}
h2{font-size:22px}
p{color:var(--muted); line-height:1.55}

.grid{
  display:grid;
  grid-template-columns: repeat(12, 1fr);
  gap:16px;
}

.tile{
  grid-column: span 3;
  background: #ffffff;
  border-radius: var(--radius2);
  padding:16px;
  border:1px solid rgba(15,23,42,.08);
  box-shadow: 0 8px 24px rgba(2,6,23,.06);
  transition: transform .08s ease, box-shadow .12s ease;
}
.tile:hover{
  transform: translateY(-2px);
  box-shadow: 0 16px 30px rgba(2,6,23,.10);
  text-decoration:none;
}

.tile .k{color:var(--muted); font-size:13px; margin-bottom:6px}
.tile .v{font-weight:800; font-size:18px}

.table{
  width:100%;
  border-collapse: collapse;
  overflow:hidden;
  border-radius: var(--radius2);
  background:#fff;
  border:1px solid rgba(15,23,42,.08);
}
.table th,.table td{
  padding:12px 12px;
  border-bottom:1px solid rgba(15,23,42,.06);
  text-align:left;
  vertical-align:top;
}
.table th{font-size:13px; color:var(--muted); font-weight:800; background: rgba(15,23,42,.03)}
.table tr:last-child td{border-bottom:none}

.field{margin:12px 0}
label{display:block; font-size:13px; font-weight:800; color:rgba(15,23,42,.70); margin-bottom:6px}
input[type="text"],input[type="email"],input[type="password"],input[type="number"],select,textarea{
  width:100%;
  padding:12px 12px;
  border-radius: 12px;
  border:1px solid rgba(15,23,42,.14);
  background:#fff;
  outline:none;
}
textarea{min-height:120px; resize:vertical}
input:focus,select:focus,textarea:focus{
  border-color: var(--primary);
  box-shadow: 0 0 0 4px var(--ring);
}

.btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  padding:10px 14px;
  border-radius: 12px;
  border:1px solid rgba(37,99,235,.35);
  background: linear-gradient(180deg, rgba(37,99,235,.98), rgba(29,78,216,.98));
  color:#fff;
  font-weight:800;
  cursor:pointer;
}
.btn:hover{text-decoration:none; filter:brightness(1.03)}
.btn:active{transform: translateY(1px)}
.btn.secondary{
  background:#fff;
  color:var(--text);
  border-color: rgba(15,23,42,.16);
}
.btn.danger{
  background: linear-gradient(180deg, rgba(220,38,38,.98), rgba(185,28,28,.98));
  border-color: rgba(220,38,38,.35);
}

.alert{
  padding:12px 14px;
  border-radius: 12px;
  border:1px solid rgba(15,23,42,.12);
  background:#fff;
  margin: 12px 0 18px;
  font-weight:700;
}
.alert.error{border-color: rgba(220,38,38,.35); background: rgba(220,38,38,.08); color:#7f1d1d}
.alert.success{border-color: rgba(22,163,74,.35); background: rgba(22,163,74,.10); color:#14532d}
.alert.warn{border-color: rgba(217,119,6,.35); background: rgba(217,119,6,.10); color:#7c2d12}

.footer{
  margin-top:18px;
  color: rgba(255,255,255,.75);
  text-align:center;
  font-size:13px;
}

@media (max-width: 980px){
  .tile{grid-column: span 6}
}
@media (max-width: 620px){
  .container{width: calc(100% - 24px)}
  .topbar{flex-direction:column; align-items:stretch}
  .brand{justify-content:center}
  .nav{justify-content:center}
  .tile{grid-column: span 12}
}
</style>';

    echo '</head><body>';

    echo '<header class="nav"><div class="navin">';

    echo '<div class="brand">';
    echo '<a href="/" style="display:flex;align-items:center;gap:10px;">';
    echo '<span class="logo">';
    if ($logo !== '') {
        echo '<img src="/' . e($logo) . '" alt="logo">';
    }
    echo '</span>';
    echo '<span>' . e($brand) . '</span>';
    echo '</a>';
    echo '</div>';

    echo '<nav class="links">';
    if ($u) {
        if ($role === 'customer') {
            echo '<a href="/customer/index.php">Storefront</a>';
            echo '<a href="/customer/cart.php">Cart</a>';
            echo '<a href="/customer/orders.php">My Orders</a>';
        } elseif ($role === 'retailer') {
            echo '<a href="/retailer/index.php">Dashboard</a>';
            echo '<a href="/retailer/products.php">Products</a>';
            echo '<a href="/retailer/orders.php">Orders</a>';
        } elseif ($role === 'staff') {
            echo '<a href="/staff/index.php">Dashboard</a>';
            echo '<a href="/staff/orders.php">Orders</a>';
        } elseif ($role === 'admin') {
            echo '<a href="/admin/index.php">Dashboard</a>';
            echo '<a href="/admin/users_create.php">Create Users</a>';
            echo '<a href="/admin/users_manage.php">Manage Users</a>';
            echo '<a href="/admin/retailers_approve.php">Approve Retailers</a>';
            echo '<a href="/admin/products_manage.php">Products</a>';
        }
        echo '<a href="/logout.php">Logout</a>';
    } else {
        echo '<a href="/home.php">Home</a>';
        echo '<a href="/login.php">Login</a>';
        echo '<a href="/register.php">Register</a>';
        echo '<a href="/retailer_apply.php">Retailer Apply</a>';
    }
    echo '</nav>';

    echo '</div></header>';

    echo '<main class="wrap">';
}

function layout_footer(): void
{
    $brand = defined('APP_BRAND') ? APP_BRAND : 'Business Store';

    echo '</main>';

    echo '<footer class="foot"><div class="footin">';
    echo '<div>© ' . date('Y') . ' ' . e($brand) . ' · PHP + MySQL</div>';
    echo '<div>All rights reserved</div>';
    echo '</div></footer>';

    echo '</body></html>';
}

