<?php
session_start();

/* ── Language ── */
$allowed_langs = ['at', 'en', 'cz'];
if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed_langs, true)) {
  $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'at';
if (!in_array($lang, $allowed_langs, true)) $lang = 'at';

$t = require __DIR__ . '/lang/' . $lang . '.php';

$html_lang_map = ['at' => 'de', 'en' => 'en', 'cz' => 'cs'];
$html_lang = $html_lang_map[$lang];

function lang_qs(string $lang): string {
  return '&amp;lang=' . $lang;
}

/* ── Routing ── */
$allowed_pages = ['home', 'collection', 'product', 'booking', 'cart', 'checkout', 'order-confirm'];
$page = $_GET['p'] ?? 'home';
if (!in_array($page, $allowed_pages, true)) $page = 'home';

$cat = $_GET['cat'] ?? 'Alle';
$id  = $_GET['id']  ?? null;

/* ── Product data (from DB) ── */
require_once __DIR__ . '/includes/db.php';
try {
    $products = db()->query('SELECT id, brand, name, cat, price, color, tag, img FROM products ORDER BY brand, name')->fetchAll();
} catch (Exception $e) { $products = []; }

/* Only resolve product when needed */
$product = null;
if ($page === 'product' || $page === 'collection') {
  foreach ($products as $p) {
    if ($p['id'] === $id) { $product = $p; break; }
  }
  if (!$product) $product = $products[0];
}

/* ── Cart ── */
if (isset($_POST['add_to_cart'])) {
  $pid = $_POST['product_id'] ?? '';
  foreach ($products as $_p) {
    if ($_p['id'] === $pid) {
      cart_add($pid, parse_price($_p['price']));
      $_SESSION['cart_count'] = cart_count();
      break;
    }
  }
  header('Location: ?p=product&id=' . urlencode($pid) . '&added=1&lang=' . $lang);
  exit;
}

$cart = $_SESSION['cart_count'] ?? 0;

/* ── Booking init (logic handled inside pages/booking.php) ── */
$booking_done = false;
$errors = [];

/* ── Meta ── */
$titles = [
  'home'       => $t['title_home'],
  'collection' => $t['title_collection'],
  'product'    => ($product ? $product['brand'] . ' ' . $product['name'] : '') . ' — expert·optic',
  'booking'    => $t['title_booking'],
];
$title = $titles[$page] ?? 'expert·optic';

$descs = [
  'home'       => $t['meta_home'],
  'collection' => $t['meta_collection'],
  'product'    => ($product ? htmlspecialchars($product['brand'] . ' ' . $product['name']) . ' — ' : '') . $t['meta_product'],
  'booking'    => $t['meta_booking'],
];
$desc = $descs[$page] ?? $descs['home'];
?>
<!DOCTYPE html>
<html lang="<?= $html_lang ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="description" content="<?= $desc ?>">
  <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
  <meta property="og:description" content="<?= $desc ?>">
  <meta property="og:type" content="website">
  <meta name="theme-color" content="#2f9bd6">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="site.css">
  <script>
    /* Apply saved preferences before first paint to avoid FOUC */
    (function() {
      var sizes = { a: '', aa: '112.5%', aaa: '125%' };
      var savedZoom = (typeof localStorage !== 'undefined' && localStorage.getItem('textZoom')) || 'a';
      if (sizes[savedZoom]) document.documentElement.style.fontSize = sizes[savedZoom];
      if (typeof localStorage !== 'undefined' && localStorage.getItem('highContrast') === '1') {
        document.documentElement.classList.add('high-contrast');
      }
    })();
  </script>
  <script src="https://unpkg.com/lucide@0.474.0/dist/umd/lucide.min.js" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      lucide.createIcons();

      // Scroll-reveal
      const io = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('revealed'); io.unobserve(e.target); } });
      }, { threshold: 0.08 });
      document.querySelectorAll('.reveal').forEach(el => io.observe(el));

      // Back to top + scrolled header
      const btt = document.getElementById('back-to-top');
      const hdr = document.querySelector('.site-header');
      window.addEventListener('scroll', () => {
        btt.classList.toggle('visible', scrollY > 500);
        hdr.classList.toggle('scrolled', scrollY > 10);
      }, { passive: true });
      btt.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

      // Hamburger menu
      const menuBtn  = document.getElementById('mobile-menu-btn');
      const mainNav  = document.getElementById('main-nav');
      const backdrop = document.getElementById('nav-backdrop');
      function openNav() {
        mainNav.classList.add('open');
        backdrop.classList.add('visible');
        menuBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
      }
      function closeNav() {
        mainNav.classList.remove('open');
        backdrop.classList.remove('visible');
        menuBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
      }
      if (menuBtn) {
        menuBtn.addEventListener('click', () =>
          mainNav.classList.contains('open') ? closeNav() : openNav()
        );
      }
      if (backdrop) backdrop.addEventListener('click', closeNav);
      mainNav && mainNav.querySelectorAll('.nav-link').forEach(link =>
        link.addEventListener('click', closeNav)
      );
      document.addEventListener('keydown', e => { if (e.key === 'Escape') closeNav(); });

      // Product image thumbnail switcher
      const mainPhoto = document.querySelector('.product-photo');
      document.querySelectorAll('.product-img-thumb').forEach(btn => {
        btn.addEventListener('mouseenter', () => {
          document.querySelectorAll('.product-img-thumb').forEach(b => b.classList.remove('product-img-thumb--active'));
          btn.classList.add('product-img-thumb--active');
          if (mainPhoto) mainPhoto.src = btn.dataset.img;
        });
      });

      // AI try-on color switcher
      const aiGlasses = document.getElementById('ai-glasses');
      document.querySelectorAll('.ai-color-thumb').forEach(btn => {
        btn.addEventListener('click', () => {
          document.querySelectorAll('.ai-color-thumb').forEach(b => b.classList.remove('ai-color-thumb--active'));
          btn.classList.add('ai-color-thumb--active');
          if (aiGlasses) aiGlasses.style.color = btn.dataset.color;
        });
      });

      // High contrast
      const contrastBtn = document.getElementById('contrast-toggle');
      if (contrastBtn) {
        const hcOn = document.documentElement.classList.contains('high-contrast');
        contrastBtn.setAttribute('aria-pressed', hcOn ? 'true' : 'false');
        contrastBtn.addEventListener('click', () => {
          const on = document.documentElement.classList.toggle('high-contrast');
          if (typeof localStorage !== 'undefined') localStorage.setItem('highContrast', on ? '1' : '0');
          contrastBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
      }

      // Text zoom
      const zoomSizes = { a: '', aa: '112.5%', aaa: '125%' };
      function applyZoom(level) {
        document.documentElement.style.fontSize = zoomSizes[level] || '';
        document.querySelectorAll('.text-zoom__btn').forEach(btn => {
          const active = btn.dataset.zoom === level;
          btn.classList.toggle('text-zoom__btn--active', active);
          btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
      }
      const savedZoom = (typeof localStorage !== 'undefined' && localStorage.getItem('textZoom')) || 'a';
      applyZoom(savedZoom);
      document.querySelectorAll('.text-zoom__btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const level = btn.dataset.zoom;
          if (typeof localStorage !== 'undefined') localStorage.setItem('textZoom', level);
          applyZoom(level);
        });
      });
    });
  </script>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main id="main-content">
<?php
switch ($page) {
  case 'collection':    include __DIR__ . '/pages/collection.php';    break;
  case 'product':       include __DIR__ . '/pages/product.php';       break;
  case 'booking':       include __DIR__ . '/pages/booking.php';       break;
  case 'cart':          include __DIR__ . '/pages/cart.php';          break;
  case 'checkout':      include __DIR__ . '/pages/checkout.php';      break;
  case 'order-confirm': include __DIR__ . '/pages/order-confirm.php'; break;
  default:              include __DIR__ . '/pages/home.php';          break;
}
?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- Floating phone button -->
<a href="tel:+420603419882" class="float-call" aria-label="<?= htmlspecialchars($t['aria_call']) ?>">
  <i data-lucide="phone"></i>
  <span>+420 603 419 882</span>
</a>

<!-- Back to top -->
<button id="back-to-top" class="back-to-top" aria-label="<?= htmlspecialchars($t['aria_back_to_top']) ?>">
  <i data-lucide="arrow-up"></i>
</button>

<!-- Hidden admin entry (not visible, not indexed) -->
<a href="admin/" aria-hidden="true" tabindex="-1" style="position:absolute;width:1px;height:1px;overflow:hidden;opacity:0;pointer-events:none">admin</a>

</body>
</html>
