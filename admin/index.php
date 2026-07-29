<?php
session_start();
/* Buffer output so page files can still call header() for POST→redirect flows */
ob_start();
require_once __DIR__ . '/config.php';

/* ── Auth guard ── */
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

/* ── Language ── */
if (isset($_GET['set_lang'])) {
    $set = preg_replace('/[^a-z]/', '', $_GET['set_lang']);
    if (in_array($set, ['de', 'cz', 'en'])) $_SESSION['admin_lang'] = $set;
    $redir = '?page=' . preg_replace('/[^a-z-]/', '', $_GET['page'] ?? 'dashboard');
    header('Location: ' . $redir);
    exit;
}
$admin_lang = $_SESSION['admin_lang'] ?? 'de';
$al = require __DIR__ . '/lang/' . $admin_lang . '.php';

/* ── Routing ── */
$page = preg_replace('/[^a-z-]/', '', $_GET['page'] ?? 'dashboard');
$allowed = ['dashboard', 'products', 'bookings', 'orders', 'users', 'settings', 'sitemap', 'about', 'vouchers', 'newsletter'];
if (!in_array($page, $allowed)) $page = 'dashboard';

$page_titles = [
    'dashboard' => $al['title_dashboard'],
    'products'  => $al['title_products'],
    'bookings'  => $al['title_bookings'],
    'orders'    => $al['title_orders'],
    'users'     => $al['title_users'],
    'settings'  => $al['title_settings'],
    'sitemap'   => $al['title_sitemap'],
    'about'     => $al['title_about'] ?? 'About',
    'vouchers'  => $al['title_vouchers'] ?? 'Vouchers',
    'newsletter'=> $al['title_newsletter'] ?? 'Newsletter',
];

/* ── Shared data ── */
try {
    $products = db()->query('SELECT * FROM products ORDER BY brand, name')->fetchAll();
} catch (Exception $e) { $products = []; }
$_bookings_file = __DIR__ . '/../data/bookings.json';
$bookings = [];
if (file_exists($_bookings_file)) {
    $_d = json_decode(file_get_contents($_bookings_file), true);
    if (is_array($_d)) $bookings = $_d;
}
$new_bookings = count(array_filter($bookings, fn($b) => ($b['status'] ?? 'new') === 'new'));

/* ── Inline SVG icons (avoids external dependency) ── */
function icon(string $name): string {
    $icons = [
        'grid'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>',
        'glasses'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="15" r="4"/><circle cx="18" cy="15" r="4"/><path d="M2 15h0M10 15h4M22 15h0M6 11V7M18 11V7M6 7h12"/></svg>',
        'calendar'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        'menu'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6"  x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
        'logout'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
        'external'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>',
        'plus'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
        'edit'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
        'trash'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>',
        'check'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
        'users'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'tag'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
        'settings'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
        'shield'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'key'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>',
        'package'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
        'map'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>',
    ];
    return $icons[$name] ?? '';
}

$html_lang = ['de' => 'de', 'cz' => 'cs', 'en' => 'en'][$admin_lang] ?? 'de';
?>
<!DOCTYPE html>
<html lang="<?= $html_lang ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_titles[$page]) ?> — expert·optic Admin</title>
  <link rel="stylesheet" href="admin.css">
  <meta name="robots" content="noindex,nofollow">
</head>
<body>
<div class="a-layout">

  <!-- Overlay for mobile -->
  <div class="a-overlay" id="a-overlay"></div>

  <!-- ── Sidebar ── -->
  <aside class="a-sidebar" id="a-sidebar">
    <a href="index.php" class="a-sidebar__logo">
      <img src="../assets/logo.png" alt="expert·optic">
      <span class="a-sidebar__logo-label">Admin</span>
    </a>

    <nav class="a-sidebar__nav">
      <span class="a-nav-section"><?= htmlspecialchars($al['nav_overview']) ?></span>
      <a href="index.php?page=dashboard" class="a-nav-link<?= $page==='dashboard'?' active':'' ?>">
        <?= icon('grid') ?> <?= htmlspecialchars($al['nav_dashboard']) ?>
      </a>

      <span class="a-nav-section"><?= htmlspecialchars($al['nav_management']) ?></span>
      <a href="index.php?page=products" class="a-nav-link<?= $page==='products'?' active':'' ?>">
        <?= icon('glasses') ?> <?= htmlspecialchars($al['nav_products']) ?>
        <span class="a-nav-badge"><?= count($products) ?></span>
      </a>
      <a href="index.php?page=bookings" class="a-nav-link<?= $page==='bookings'?' active':'' ?>">
        <?= icon('calendar') ?> <?= htmlspecialchars($al['nav_bookings']) ?>
        <?php if ($new_bookings > 0): ?>
          <span class="a-nav-badge"><?= $new_bookings ?></span>
        <?php endif; ?>
      </a>
      <?php
        try {
          $new_orders = (int)db()->query("SELECT COUNT(*) FROM orders WHERE status='new'")->fetchColumn();
        } catch (Exception $e) { $new_orders = 0; }
      ?>
      <a href="index.php?page=orders" class="a-nav-link<?= $page==='orders'?' active':'' ?>">
        <?= icon('package') ?> <?= htmlspecialchars($al['nav_orders']) ?>
        <?php if ($new_orders > 0): ?>
          <span class="a-nav-badge"><?= $new_orders ?></span>
        <?php endif; ?>
      </a>
      <a href="index.php?page=vouchers" class="a-nav-link<?= $page==='vouchers'?' active':'' ?>">
        <?= icon('tag') ?> <?= htmlspecialchars($al['nav_vouchers'] ?? 'Vouchers') ?>
      </a>
      <a href="index.php?page=newsletter" class="a-nav-link<?= $page==='newsletter'?' active':'' ?>">
        <?= icon('users') ?> <?= htmlspecialchars($al['nav_newsletter'] ?? 'Newsletter') ?>
      </a>

      <span class="a-nav-section"><?= htmlspecialchars($al['nav_system']) ?></span>
      <a href="index.php?page=users" class="a-nav-link<?= $page==='users'?' active':'' ?>">
        <?= icon('users') ?> <?= htmlspecialchars($al['nav_users']) ?>
      </a>
      <a href="index.php?page=settings" class="a-nav-link<?= $page==='settings'?' active':'' ?>">
        <?= icon('settings') ?> <?= htmlspecialchars($al['nav_settings']) ?>
      </a>
      <a href="index.php?page=sitemap" class="a-nav-link<?= $page==='sitemap'?' active':'' ?>">
        <?= icon('map') ?> <?= htmlspecialchars($al['nav_sitemap']) ?>
      </a>
      <a href="index.php?page=about" class="a-nav-link<?= $page==='about'?' active':'' ?>">
        <?= icon('shield') ?> <?= htmlspecialchars($al['nav_about'] ?? 'About') ?>
      </a>

      <span class="a-nav-section"><?= htmlspecialchars($al['nav_website']) ?></span>
      <a href="../index.php" target="_blank" class="a-nav-link">
        <?= icon('external') ?> <?= htmlspecialchars($al['nav_site']) ?>
      </a>
      <a href="../index.php?p=brno" target="_blank" class="a-nav-link">
        <?= icon('external') ?> <?= htmlspecialchars($al['nav_studio_brno']) ?>
      </a>
    </nav>

    <div class="a-sidebar__footer">
      <div class="a-sidebar__avatar">TS</div>
      <div>
        <div class="a-sidebar__user-name">Thomas Scheibl</div>
        <div class="a-sidebar__user-role"><?= htmlspecialchars($al['role_admin']) ?></div>
      </div>
      <a href="logout.php" class="a-sidebar__logout" title="<?= htmlspecialchars($al['logout_title']) ?>">
        <?= icon('logout') ?>
      </a>
    </div>
  </aside>

  <!-- ── Main ── -->
  <div class="a-main">

    <!-- Topbar -->
    <header class="a-topbar">
      <button class="a-topbar__toggle" id="a-toggle" aria-label="<?= htmlspecialchars($al['menu_aria']) ?>">
        <?= icon('menu') ?>
      </button>
      <h1 class="a-topbar__title"><?= htmlspecialchars($page_titles[$page]) ?></h1>
      <div class="a-topbar__actions">
        <!-- Language switcher -->
        <div class="a-lang-switch">
          <?php foreach (['de' => 'DE', 'cz' => 'CZ', 'en' => 'EN'] as $code => $label): ?>
            <a href="?page=<?= $page ?>&set_lang=<?= $code ?>"
               class="a-lang-btn<?= $admin_lang === $code ? ' active' : '' ?>"><?= $label ?></a>
          <?php endforeach; ?>
        </div>
        <a href="../index.php" target="_blank" class="a-topbar__site-link">
          <?= icon('external') ?> <?= htmlspecialchars($al['topbar_website']) ?>
        </a>
        <?php if ($page === 'products'): ?>
          <button class="a-topbar__btn" onclick="showProductForm()">
            <?= icon('plus') ?> <?= htmlspecialchars($al['topbar_add_product']) ?>
          </button>
        <?php endif; ?>
      </div>
    </header>

    <!-- Page content -->
    <main class="a-content">
      <?php include __DIR__ . '/pages/' . $page . '.php'; ?>
    </main>

  </div><!-- /a-main -->
</div><!-- /a-layout -->

<script>
const sidebar  = document.getElementById('a-sidebar');
const overlay  = document.getElementById('a-overlay');
const toggle   = document.getElementById('a-toggle');
function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('visible'); }
function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('visible'); }
toggle.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
overlay.addEventListener('click', closeSidebar);
</script>
</body>
</html>
