<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../config.php';

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
        --bg:#f4f6fb;
        --card:#ffffff;
        --text:#0f172a;
        --muted:#64748b;

        --nav1:#0b5ed7;
        --nav2:#0a3d91;
        --link:#eaf2ff;
        --linkHover:#ffffff;

        --shadow:0 10px 28px rgba(2,6,23,.10);
        --radius:14px;
        --border:#e6e9ee;
      }

      *{box-sizing:border-box}
      html,body{height:100%}
      body{
        margin:0;
        font-family: Arial, sans-serif;
        background: var(--bg);
        color: var(--text);

        /* Sticky footer */
        display:flex;
        flex-direction:column;
        min-height:100vh;
      }

      /* Header */
      .nav{
        background: linear-gradient(90deg, var(--nav1), var(--nav2));
        box-shadow: var(--shadow);
      }
      .navin{
        max-width:1100px;
        margin:0 auto;
        padding:12px 16px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
      }

      .brand{
        display:flex;
        align-items:center;
        gap:10px;
        min-width:220px;
      }
      .brand a{
        color: var(--linkHover);
        text-decoration:none;
        font-weight:bold;
        letter-spacing:.2px;
      }
      .logo{
        width:28px;
        height:28px;
        border-radius:8px;
        background: rgba(255,255,255,.18);
        display:flex;
        align-items:center;
        justify-content:center;
        overflow:hidden;
      }
      .logo img{
        width:100%;
        height:100%;
        object-fit:contain;
        display:block;
      }

      .links{
        display:flex;
        flex-wrap:wrap;
        justify-content:flex-end;
        gap:10px;
      }
      .links a{
        color: var(--link);
        text-decoration:none;
        font-weight:bold;
        font-size:13px;
        padding:8px 10px;
        border-radius:10px;
        background: rgba(255,255,255,.10);
      }
      .links a:hover{
        color: var(--linkHover);
        background: rgba(255,255,255,.18);
      }

      /* Main area grows to push footer down */
      main.wrap{
        flex:1;
        width:100%;
        max-width:1100px;
        margin:22px auto;
        padding:0 16px;
      }

      .card{
        background:var(--card);
        border-radius:var(--radius);
        box-shadow: var(--shadow);
        padding:16px;
      }
      .muted{color:var(--muted);font-size:13px}

      /* Generic UI */
      a{color:#0b5ed7;text-decoration:none}
      h1,h2,h3{margin:0 0 12px}
      p{margin:8px 0}
      ul{margin:10px 0 0 18px}
      input,select,textarea{
        padding:10px;
        border:1px solid #ccd2da;
        border-radius:10px;
        width:100%;
      }
      textarea{min-height:90px;resize:vertical}
      button{
        padding:10px 14px;
        border:0;
        border-radius:10px;
        cursor:pointer;
        font-weight:bold;
      }
      table{
        width:100%;
        border-collapse:collapse;
        margin-top:12px;
        background:#fff;
        border-radius:12px;
        overflow:hidden;
        box-shadow: var(--shadow);
      }
      th,td{
        padding:10px;
        border-bottom:1px solid var(--border);
        text-align:left;
        font-size:14px;
      }
      th{background:#f2f4f7}
      .msg{padding:10px;border-radius:10px;margin-top:12px}
      .ok{background:#e9fff0;color:#135a2e}
      .err{background:#ffe8e8;color:#7a1d1d}
      .pill{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;background:#eef2f7}
      .btn{display:inline-block;padding:9px 12px;border-radius:10px;font-weight:bold;font-size:13px;text-decoration:none}
      .btn-ok{background:#e9fff0;color:#135a2e}
      .btn-warn{background:#ffe8e8;color:#7a1d1d}
      .btn-info{background:#eef2ff;color:#1f2a6b}
      .row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
      .spacer{height:12px}

      /* Footer */
      footer.foot{
        width:100%;
        margin-top:auto;
        padding:16px 0;
        color: var(--muted);
        font-size:13px;
      }
      footer .footin{
        max-width:1100px;
        margin:0 auto;
        padding:0 16px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
      }

        /* Buttons + inputs */
.input{
  padding:10px;
  border:1px solid rgba(15,23,42,.12);
  border-radius:12px;
  background:#fff;
  min-width:180px;
}
.filter-row{
  display:flex;
  gap:10px;
  align-items:center;
  flex-wrap:wrap;
}
.btn{
  display:inline-block;
  padding:10px 14px;
  border-radius:12px;
  border:0;
  cursor:pointer;
  font-weight:bold;
  text-decoration:none;
}
.btn.primary{
  background: rgba(255,255,255,.18);
  color:#fff;
}
.btn.primary:hover{
  background: rgba(255,255,255,.28);
}

/* Nice link style everywhere */
a{ text-decoration:none; }
a:hover{ text-decoration:none; }

/* Tile menus + product cards */
.grid{ display:grid; gap:14px; }
.grid.menu{ grid-template-columns:repeat(3, 1fr); }
.grid.products{ grid-template-columns:repeat(4, 1fr); }

.tile{
  background: var(--card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow:hidden;
  border:1px solid rgba(15,23,42,.06);
  transition: transform .12s ease, box-shadow .12s ease;
}
.tile:hover{
  transform: translateY(-2px);
  box-shadow: 0 14px 36px rgba(2,6,23,.14);
}

.tile .tile-body{ padding:12px; }
.tile-title{
  display:block;
  font-weight:800;
  color: var(--text);
  margin-bottom:6px;
}
.tile-title:hover{ color:#0b5ed7; }

.tile-img{
  height:160px;
  background:#eef2f7;
  display:flex;
  align-items:center;
  justify-content:center;
}
.tile-img img{
  max-width:100%;
  max-height:100%;
  display:block;
  object-fit:contain;
}

.price{ margin-top:10px; font-weight:900; font-size:16px; }

@media(max-width:1000px){
  .grid.menu{ grid-template-columns:repeat(2,1fr); }
  .grid.products{ grid-template-columns:repeat(2,1fr); }
}
@media(max-width:520px){
  .grid.menu{ grid-template-columns:1fr; }
  .grid.products{ grid-template-columns:1fr; }
}

    </style>';

    echo '</head><body>';

    echo '<header class="nav"><div class="navin">';

    echo '<div class="brand">';
    echo '<a href="/business_store/" style="display:flex;align-items:center;gap:10px;">';
    echo '<span class="logo">';
    if ($logo !== '') {
        echo '<img src="/business_store/' . e($logo) . '" alt="logo">';
    }
    echo '</span>';
    echo '<span>' . e($brand) . '</span>';
    echo '</a>';
    echo '</div>';

    echo '<nav class="links">';
    if ($u) {
        if ($role === 'customer') {
            echo '<a href="/business_store/customer/index.php">Storefront</a>';
            echo '<a href="/business_store/customer/cart.php">Cart</a>';
            echo '<a href="/business_store/customer/orders.php">My Orders</a>';
        } elseif ($role === 'retailer') {
            echo '<a href="/business_store/retailer/index.php">Dashboard</a>';
            echo '<a href="/business_store/retailer/products.php">Products</a>';
            echo '<a href="/business_store/retailer/orders.php">Orders</a>';
        } elseif ($role === 'staff') {
            echo '<a href="/business_store/staff/index.php">Dashboard</a>';
            echo '<a href="/business_store/staff/orders.php">Orders</a>';
        } elseif ($role === 'admin') {
            echo '<a href="/business_store/admin/index.php">Dashboard</a>';
            echo '<a href="/business_store/admin/users_create.php">Create Users</a>';
            echo '<a href="/business_store/admin/users_manage.php">Manage Users</a>';
            echo '<a href="/business_store/admin/retailers_approve.php">Approve Retailers</a>';
            echo '<a href="/business_store/admin/products_manage.php">Products</a>';
        }
        echo '<a href="/business_store/logout.php">Logout</a>';
    } else {
        echo '<a href="/business_store/home.php">Home</a>';
        echo '<a href="/business_store/login.php">Login</a>';
        echo '<a href="/business_store/register.php">Register</a>';
        echo '<a href="/business_store/retailer_apply.php">Retailer Apply</a>';
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
