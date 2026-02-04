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
    --bg:#0b1220;
    --panel:#111a2e;
    --panel2:#0f1830;
    --text:#e8ecf5;
    --muted:#a9b3c7;
    --line:rgba(255,255,255,0.08);
    --accent:#4f83ff;
    --accent2:#16c784;
    --warn:#ffb020;
    --danger:#ff5470;
    --radius:14px;
    --shadow:0 10px 30px rgba(0,0,0,0.35);
  }
  *{box-sizing:border-box}
  html,body{height:100%}
  body{
    margin:0;
    font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
    color:var(--text);
    background:radial-gradient(900px 500px at 10% 0%, rgba(79,131,255,0.35), transparent 60%),
              radial-gradient(900px 500px at 90% 20%, rgba(22,199,132,0.25), transparent 60%),
              var(--bg);
  }
  a{color:inherit; text-decoration:none}
  a:hover{text-decoration:underline}
  .wrap{max-width:1100px; margin:0 auto; padding:22px}
  .card{
    background:linear-gradient(180deg, rgba(17,26,46,0.95), rgba(15,24,48,0.95));
    border:1px solid var(--line);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
  }
  .topbar{
    position:sticky; top:0; z-index:10;
    backdrop-filter: blur(10px);
    background:rgba(11,18,32,0.65);
    border-bottom:1px solid var(--line);
  }
  .topbar .row{display:flex; align-items:center; justify-content:space-between; gap:12px}
  .brand{display:flex; align-items:center; gap:12px; font-weight:800; letter-spacing:0.2px}
  .brand img{width:40px; height:40px; border-radius:12px; object-fit:cover; border:1px solid var(--line)}
  .nav{display:flex; flex-wrap:wrap; gap:10px; align-items:center}
  .nav a{padding:8px 12px; border-radius:999px; border:1px solid transparent; color:var(--muted)}
  .nav a:hover{border-color:var(--line); color:var(--text); text-decoration:none}
  .nav a.active{border-color:rgba(79,131,255,0.6); color:var(--text)}

  .hero{padding:24px; margin-top:16px}
  .hero h1{margin:0 0 6px; font-size:28px}
  .hero p{margin:0; color:var(--muted); line-height:1.5}

  .grid{display:grid; gap:14px}
  .grid.cols-2{grid-template-columns:repeat(2, minmax(0,1fr))}
  .grid.cols-3{grid-template-columns:repeat(3, minmax(0,1fr))}
  @media(max-width:900px){.grid.cols-3{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:650px){.grid.cols-2,.grid.cols-3{grid-template-columns:1fr}}

  .tile{padding:16px; border-radius:var(--radius); border:1px solid var(--line); background:rgba(255,255,255,0.03)}
  .tile h3{margin:0 0 6px; font-size:16px}
  .tile p{margin:0; color:var(--muted); font-size:14px; line-height:1.4}

  .btn{
    display:inline-flex; align-items:center; justify-content:center;
    gap:8px; padding:10px 14px; border-radius:12px;
    border:1px solid var(--line);
    background:rgba(255,255,255,0.04);
    color:var(--text);
    cursor:pointer;
    font-weight:700;
  }
  .btn:hover{text-decoration:none; background:rgba(255,255,255,0.06)}
  .btn.primary{background:rgba(79,131,255,0.18); border-color:rgba(79,131,255,0.6)}
  .btn.success{background:rgba(22,199,132,0.12); border-color:rgba(22,199,132,0.55)}
  .btn.danger{background:rgba(255,84,112,0.12); border-color:rgba(255,84,112,0.55)}

  .form{display:grid; gap:10px}
  label{font-size:13px; color:var(--muted)}
  input,select,textarea{
    width:100%; padding:11px 12px; border-radius:12px;
    border:1px solid var(--line);
    background:rgba(255,255,255,0.03);
    color:var(--text);
    outline:none;
  }
  textarea{min-height:110px; resize:vertical}
  input:focus,select:focus,textarea:focus{border-color:rgba(79,131,255,0.75)}

  .alert{padding:12px 14px; border-radius:12px; border:1px solid var(--line)}
  .alert.ok{border-color:rgba(22,199,132,0.55); background:rgba(22,199,132,0.10)}
  .alert.err{border-color:rgba(255,84,112,0.55); background:rgba(255,84,112,0.10)}

  table{width:100%; border-collapse:separate; border-spacing:0; overflow:hidden; border-radius:14px; border:1px solid var(--line)}
  th,td{padding:12px 10px; border-bottom:1px solid var(--line); text-align:left}
  th{font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:var(--muted); background:rgba(255,255,255,0.03)}
  tr:last-child td{border-bottom:none}

  .badge{display:inline-flex; padding:4px 10px; border-radius:999px; font-size:12px; border:1px solid var(--line); color:var(--muted)}
  .badge.ok{border-color:rgba(22,199,132,0.55); color:#b9f6d5}
  .badge.warn{border-color:rgba(255,176,32,0.55); color:#ffe0a3}
  .badge.danger{border-color:rgba(255,84,112,0.55); color:#ffd0d9}

  .footer{color:var(--muted); padding:18px 0; text-align:center}
</style>';

    echo '</head><body>';

    echo '<div class="topbar"><div class="wrap row">';

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

    echo '<nav class="nav">';
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

    echo '</div></div>';

    echo '<main class="wrap">';
}

function layout_footer(): void
{
    $brand = defined('APP_BRAND') ? APP_BRAND : 'Business Store';

    echo '</main>';

    echo '<footer class="footer"><div class="wrap">';
    echo '© ' . date('Y') . ' ' . e($brand) . ' · PHP + MySQL';
    echo '</div></footer>';

    echo '</body></html>';
}

