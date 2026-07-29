<?php
ob_start();
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

/* ── Admin-configured site info (contact + analytics) ── */
$_site_settings = [];
$_site_settings_file = __DIR__ . '/data/settings.json';
if (file_exists($_site_settings_file)) {
  $_decoded = json_decode(file_get_contents($_site_settings_file), true);
  if (is_array($_decoded)) $_site_settings = $_decoded;
}
$site_info = [
  'studio_name' => $_site_settings['site_studio_name'] ?? 'Optické a optometristické studio',
  'owner_name'  => $_site_settings['site_owner_name']  ?? 'Thomas Scheibl',
  'street'      => $_site_settings['site_street']      ?? 'Hlavní 131',
  'city_postal' => $_site_settings['site_city_postal'] ?? '624 00 Brno-Komín',
  'phone'       => $_site_settings['site_phone']       ?? '+420 603 419 882',
  'email'       => $_site_settings['site_email']       ?? 'brno@tstoptik.com',
  'ga_id'       => $_site_settings['site_ga_id']       ?? '',
];

require_once __DIR__ . '/includes/recaptcha.php';

/* ── Routing ── */
$allowed_pages = ['home', 'collection', 'product', 'booking', 'cart', 'checkout', 'order-confirm', 'contact-lenses', 'sport-glasses', 'register', 'login', 'logout', 'account', 'datenschutz', 'agb'];
$page = $_GET['p'] ?? 'home';
if (!in_array($page, $allowed_pages, true)) $page = 'home';

$cat = $_GET['cat'] ?? 'Alle';
$id  = $_GET['id']  ?? null;

/* ── Product data (from DB) ── */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/customer_auth.php';
$customer = customer_current();
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
  <?php if (!empty($site_info['ga_id']) && preg_match('/^G-[A-Z0-9]{4,20}$/i', $site_info['ga_id'])):
    $ga = htmlspecialchars($site_info['ga_id'], ENT_QUOTES); ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $ga ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?= $ga ?>');
  </script>
  <?php endif; ?>
  <?php
  /* Cache-bust local static assets with filemtime so every edit ships a unique URL. */
  $asset_ver = function (string $rel): string {
      $abs = __DIR__ . '/' . ltrim($rel, '/');
      $mt  = @filemtime($abs);
      return $rel . '?v=' . ($mt ?: time());
  };
  ?>
  <link rel="stylesheet" href="<?= $asset_ver('css/styles.css') ?>">
  <link rel="stylesheet" href="<?= $asset_ver('css/site.css') ?>">
  <script src="<?= $asset_ver('assets/js/preload.js') ?>"></script>
  <script src="https://unpkg.com/lucide@0.474.0/dist/umd/lucide.min.js" defer></script>
  <script src="<?= $asset_ver('assets/js/site.js') ?>" defer></script>
  <?= recaptcha_script_tag() ?>
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
  case 'order-confirm':   include __DIR__ . '/pages/order-confirm.php';   break;
  case 'contact-lenses': include __DIR__ . '/pages/contact-lenses.php'; break;
  case 'sport-glasses':  include __DIR__ . '/pages/sport-glasses.php';  break;
  case 'register':      include __DIR__ . '/pages/register.php';       break;
  case 'login':         include __DIR__ . '/pages/login.php';          break;
  case 'logout':        include __DIR__ . '/pages/logout.php';         break;
  case 'account':       include __DIR__ . '/pages/account.php';        break;
  case 'datenschutz':   include __DIR__ . '/pages/datenschutz.php';    break;
  case 'agb':           include __DIR__ . '/pages/agb.php';            break;
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

<!-- Hidden admin entry (not visible, not indexed; keyboard shortcut lives in site.js) -->
<a href="admin/" aria-hidden="true" tabindex="-1" class="admin-shortcut">admin</a>

</body>
</html>
<?php ob_end_flush(); ?>
