<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mail.php';

$items  = cart_items();
$errors = $errors ?? [];

$fmt_money = fn(float $n): string => fmt_display($n, $lang);

/* Admin-configured shipping (falls back to lang defaults if unset) */
$_ship_settings_file = __DIR__ . '/../data/settings.json';
$_ship_settings = [];
if (file_exists($_ship_settings_file)) {
    $_decoded = json_decode(file_get_contents($_ship_settings_file), true);
    if (is_array($_decoded)) $_ship_settings = $_decoded;
}

/* Admin ship settings are authored in CZK (see settings.php label). Lang fallbacks
   are per-language display units. Normalise everything to EUR so it composes with
   cart_total (EUR). */
$czk_to_eur     = fn(float $n): float => $n / EUR_TO_CZK;
$lang_fallback_to_eur = fn(float $n): float => $lang === 'cz' ? $n / EUR_TO_CZK : $n;

$bali_cost      = isset($_ship_settings['ship_balikovna_cost'])
                  ? $czk_to_eur((float)$_ship_settings['ship_balikovna_cost'])
                  : $lang_fallback_to_eur((float)($t['co_ship_bali_cost'] ?? 89));
$bali_home_cost = isset($_ship_settings['ship_balikovna_home_cost'])
                  ? $czk_to_eur((float)$_ship_settings['ship_balikovna_home_cost'])
                  : $lang_fallback_to_eur((float)($t['co_ship_home_cost'] ?? 119));
$free_threshold = $czk_to_eur((float)($_ship_settings['ship_free_threshold'] ?? 0));

$_cart_total_for_ship = cart_total($items);
$_free_shipping = $free_threshold > 0 && $_cart_total_for_ship >= $free_threshold;

$shipping_prices = [
    'personal'       => 0,
    'balikovna'      => $_free_shipping ? 0 : $bali_cost,
    'balikovna_home' => $_free_shipping ? 0 : $bali_home_cost,
];

$shipping_enabled = [
    'personal'       => !isset($_ship_settings['ship_personal_enabled'])       || !empty($_ship_settings['ship_personal_enabled']),
    'balikovna'      => !isset($_ship_settings['ship_balikovna_enabled'])      || !empty($_ship_settings['ship_balikovna_enabled']),
    'balikovna_home' => !isset($_ship_settings['ship_balikovna_home_enabled']) || !empty($_ship_settings['ship_balikovna_home_enabled']),
];

/* ── Redirect if cart empty ── */
if (empty($items) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ?p=cart&lang=' . $lang);
    exit;
}

/* ── Handle order submission ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name     = trim($_POST['customer_name'] ?? '');
    $email    = trim($_POST['email']         ?? '');
    $phone    = trim($_POST['phone']         ?? '');
    $shipping = array_key_exists($_POST['shipping_method'] ?? '', $shipping_prices)
                ? $_POST['shipping_method'] : 'personal';
    $shipping_cost  = $shipping_prices[$shipping];
    $pickup_id      = trim($_POST['pickup_point_id']   ?? '');
    $pickup_name    = trim($_POST['pickup_point_name'] ?? '');
    $delivery_addr  = trim($_POST['delivery_address']  ?? '');
    $notes          = trim($_POST['notes']             ?? '');

    if (!$name)  $errors[] = $t['co_err_name'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = $t['co_err_email'];
    if ($shipping === 'balikovna' && !$pickup_id && !$pickup_name)
        $errors[] = $t['co_err_pickup'];
    if ($shipping === 'balikovna_home' && !$delivery_addr)
        $errors[] = $t['co_err_address'];

    if (empty($errors)) {
        $cart_total = cart_total($items);
        $total      = $cart_total + $shipping_cost;
        $pdo        = db();
        $pdo->beginTransaction();
        try {
            $has_lang = orders_has_lang();
            $cols     = 'order_number, customer_name, email, phone, total,
                         shipping_method, shipping_cost, pickup_point_id, pickup_point_name,
                         delivery_address, notes' . ($has_lang ? ', lang' : '');
            $ph       = '?,?,?,?,?,?,?,?,?,?,?' . ($has_lang ? ',?' : '');
            $vals     = ['TEMP', $name, $email, $phone ?: null, $total,
                         $shipping, $shipping_cost,
                         $pickup_id ?: null, $pickup_name ?: null,
                         $delivery_addr ?: null, $notes ?: null];
            if ($has_lang) $vals[] = $lang;
            $pdo->prepare("INSERT INTO orders ({$cols}) VALUES ({$ph})")->execute($vals);

            $oid          = (int)$pdo->lastInsertId();
            $order_number = 'EO' . date('Y') . str_pad($oid, 4, '0', STR_PAD_LEFT);
            $pdo->prepare('UPDATE orders SET order_number = ? WHERE id = ?')
                ->execute([$order_number, $oid]);

            $ins = $pdo->prepare('INSERT INTO order_items (order_id, product_id, brand, name, price, quantity) VALUES (?,?,?,?,?,?)');
            foreach ($items as $it) {
                $ins->execute([$oid, $it['product_id'], $it['brand'], $it['name'], $it['price'], $it['quantity']]);
            }

            cart_clear();
            $_SESSION['cart_count'] = 0;
            $_SESSION['last_order'] = [
                'id'              => $oid,
                'number'          => $order_number,
                'name'            => $name,
                'email'           => $email,
                'shipping'        => $shipping,
                'shipping_cost'   => $shipping_cost,
                'pickup_name'     => $pickup_name ?: null,
                'delivery_address'=> $delivery_addr ?: null,
                'cart_total'      => $cart_total,
                'total'           => $total,
                'items'           => $items,
            ];

            $pdo->commit();

            $mail_order = [
                'order_number'      => $order_number,
                'customer_name'     => $name,
                'email'             => $email,
                'phone'             => $phone,
                'total'             => $total,
                'shipping_cost'     => $shipping_cost,
                'pickup_point_name' => $pickup_name,
                'delivery_address'  => $delivery_addr,
                'notes'             => $notes,
            ];
            mail_order_placed_customer($mail_order, $items, $lang);
            mail_order_placed_admin($mail_order, $items);

            header('Location: ?p=order-confirm&lang=' . $lang);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = $t['co_err_order'];
        }
    }

    $items = cart_items();
}

$cart_total        = cart_total($items);
$_default_ship     = $shipping_enabled['personal'] ? 'personal'
                   : ($shipping_enabled['balikovna'] ? 'balikovna'
                   : 'balikovna_home');
$selected_shipping = $_POST['shipping_method'] ?? $_default_ship;
if (empty($shipping_enabled[$selected_shipping] ?? false)) $selected_shipping = $_default_ship;
$shipping_cost     = $shipping_prices[$selected_shipping] ?? 0;
$total             = $cart_total + $shipping_cost;

$v = fn(string $k, string $d = '') => htmlspecialchars($_POST[$k] ?? $d);

$personal_sub = !empty($_ship_settings['ship_personal_address'])
              ? $_ship_settings['ship_personal_address']
              : $t['co_ship_personal_sub'];
if (!empty($_ship_settings['ship_personal_hours'])) {
    $personal_sub .= ' · ' . $_ship_settings['ship_personal_hours'];
}

$shipping_options = [];
if ($shipping_enabled['personal']) {
    $shipping_options['personal'] = [
        'label' => $t['co_ship_personal_lbl'],
        'price' => $t['co_ship_personal_price'],
        'sub'   => $personal_sub,
    ];
}
if ($shipping_enabled['balikovna']) {
    $shipping_options['balikovna'] = [
        'label' => $t['co_ship_bali_lbl'],
        'price' => $_free_shipping ? $t['co_free']
                : (isset($_ship_settings['ship_balikovna_cost']) ? $fmt_money($bali_cost) : $t['co_ship_bali_price']),
        'sub'   => $t['co_ship_bali_sub'],
    ];
}
if ($shipping_enabled['balikovna_home']) {
    $shipping_options['balikovna_home'] = [
        'label' => $t['co_ship_home_lbl'],
        'price' => $_free_shipping ? $t['co_free']
                : (isset($_ship_settings['ship_balikovna_home_cost']) ? $fmt_money($bali_home_cost) : $t['co_ship_home_price']),
        'sub'   => $t['co_ship_home_sub'],
    ];
}
?>

<div class="page-wrap">
  <a href="?p=cart&amp;lang=<?= $lang ?>" class="back-link">
    <i data-lucide="arrow-left"></i>
    <?= htmlspecialchars($t['co_back']) ?>
  </a>

  <h1 class="cart-page-title"><?= htmlspecialchars($t['co_title']) ?></h1>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert--error" style="margin-bottom:.75rem">
      <i data-lucide="alert-circle"></i>
      <?= htmlspecialchars($err) ?>
    </div>
  <?php endforeach; ?>

  <form method="post" id="checkout-form" class="checkout-layout">

    <!-- Left column -->
    <div>

      <!-- Contact details -->
      <div class="card co-section">
        <h2 class="co-section__title"><?= htmlspecialchars($t['co_contact']) ?></h2>

        <label class="booking-label">
          <?= htmlspecialchars($t['co_name']) ?>
          <input type="text" name="customer_name" value="<?= $v('customer_name') ?>"
                 class="booking-input" required
                 placeholder="<?= htmlspecialchars($t['co_name_ph']) ?>">
        </label>
        <label class="booking-label">
          <?= htmlspecialchars($t['co_email']) ?>
          <input type="email" name="email" value="<?= $v('email') ?>"
                 class="booking-input" required
                 placeholder="<?= htmlspecialchars($t['co_email_ph']) ?>">
        </label>
        <label class="booking-label">
          <?= htmlspecialchars($t['co_phone']) ?>
          <input type="tel" name="phone" value="<?= $v('phone') ?>"
                 class="booking-input"
                 placeholder="<?= htmlspecialchars($t['co_phone_ph']) ?>">
        </label>
        <label class="booking-label" style="margin-bottom:0">
          <?= htmlspecialchars($t['co_notes']) ?>
          <textarea name="notes" class="booking-input" rows="2"
                    placeholder="<?= htmlspecialchars($t['co_notes_ph']) ?>"><?= $v('notes') ?></textarea>
        </label>
      </div>

      <!-- Shipping -->
      <div class="card co-section">
        <h2 class="co-section__title"><?= htmlspecialchars($t['co_shipping_title']) ?></h2>

        <?php foreach ($shipping_options as $val => $opt):
            $checked = $selected_shipping === $val; ?>
          <label class="checkout-shipping-option<?= $checked ? ' active' : '' ?>"
                 id="lbl-<?= $val ?>" onclick="selectShipping('<?= $val ?>')">
            <input type="radio" name="shipping_method" value="<?= $val ?>"
                   <?= $checked ? 'checked' : '' ?> style="flex-shrink:0;margin-top:.25rem"
                   onchange="selectShipping('<?= $val ?>')">
            <div class="co-ship-body">
              <div class="co-ship-row">
                <strong><?= htmlspecialchars($opt['label']) ?></strong>
                <span class="co-ship-price"><?= htmlspecialchars($opt['price']) ?></span>
              </div>
              <div class="co-ship-sub"><?= htmlspecialchars($opt['sub']) ?></div>
            </div>
          </label>
        <?php endforeach; ?>

        <!-- Balíkovna pickup selector -->
        <div id="picker-balikovna" class="co-picker"
             <?= $selected_shipping !== 'balikovna' ? 'style="display:none"' : '' ?>>

          <div id="bali-confirmed" class="co-bali-confirmed" style="display:none">
            <i data-lucide="map-pin"></i>
            <div class="co-bali-confirmed__info">
              <strong id="bali-confirmed-name"></strong>
              <span id="bali-confirmed-addr"></span>
            </div>
            <button type="button" class="co-bali-clear" onclick="clearPickupPoint()" aria-label="Změnit">×</button>
          </div>

          <div id="bali-iframe-wrap" class="co-bali-iframe-wrap">
            <iframe
              title="Výběr místa pro vyzvednutí zásilky"
              src="https://b2c.cpost.cz/locations/?type=BALIKOVNY&phone=true&skipLocation=false"
              allow="geolocation"
              style="width:100%; min-width:360px; min-height:800px; border:0;"
              id="bali-iframe">
            </iframe>
          </div>

          <input type="hidden" name="pickup_point_id"   id="pickup-point-id"
                 value="<?= $v('pickup_point_id') ?>">
          <input type="hidden" name="pickup_point_name" id="pickup-point-name"
                 value="<?= $v('pickup_point_name') ?>">
        </div>

        <!-- Balíkovna home address -->
        <div id="picker-balikovna_home" class="co-picker"
             <?= $selected_shipping !== 'balikovna_home' ? 'style="display:none"' : '' ?>>
          <label class="booking-label" style="margin:0">
            <?= htmlspecialchars($t['co_delivery_addr']) ?>
            <input type="text" name="delivery_address" value="<?= $v('delivery_address') ?>"
                   class="booking-input"
                   placeholder="<?= htmlspecialchars($t['co_delivery_ph']) ?>">
          </label>
        </div>

      </div><!-- /shipping -->
    </div>

    <!-- Right column: summary + submit -->
    <div class="co-aside">

      <!-- Summary -->
      <div class="card co-summary">
        <h2 class="co-summary__title"><?= htmlspecialchars($t['co_summary_title']) ?></h2>

        <?php foreach ($items as $it): ?>
          <div class="co-summary__line">
            <span><?= htmlspecialchars($it['brand'] . ' ' . $it['name']) ?> ×<?= $it['quantity'] ?></span>
            <span><?= $fmt_money($it['price'] * $it['quantity']) ?></span>
          </div>
        <?php endforeach; ?>

        <div class="co-summary__shipping">
          <span><?= htmlspecialchars($t['co_shipping_row']) ?></span>
          <span id="summary-shipping">
            <?= $shipping_cost === 0 ? htmlspecialchars($t['co_free']) : $fmt_money($shipping_cost) ?>
          </span>
        </div>

        <hr class="co-summary__divider">

        <div class="co-summary__total">
          <span><?= htmlspecialchars($t['co_total']) ?></span>
          <span id="summary-total"><?= $fmt_money($total) ?></span>
        </div>
        <p class="co-summary__vat"><?= htmlspecialchars($t['co_vat']) ?></p>
      </div>

      <!-- Payment note -->
      <div class="card co-payment-info">
        <i data-lucide="credit-card"></i>
        <?= htmlspecialchars($t['co_payment_info']) ?>
      </div>

      <button type="submit" name="place_order" class="btn btn--primary btn--lg btn--block">
        <i data-lucide="check-circle"></i>
        <?= htmlspecialchars($t['co_submit']) ?>
      </button>
      <p class="co-legal"><?= htmlspecialchars($t['co_legal']) ?></p>

    </div>

  </form>
</div>

<script>
<?php $to_disp = fn(float $n): float => $lang === 'cz' ? $n * EUR_TO_CZK : $n; ?>
var shippingPrices  = {
  personal:       0,
  balikovna:      <?= json_encode($to_disp($shipping_prices['balikovna'])) ?>,
  balikovna_home: <?= json_encode($to_disp($shipping_prices['balikovna_home'])) ?>
};
var cartTotal       = <?= json_encode($to_disp($cart_total)) ?>;
var labelFree       = <?= json_encode($t['co_free']) ?>;
var lang            = <?= json_encode($lang) ?>;

function fmtMoney(n) {
  if (lang === 'cz') {
    var whole = Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return whole + ' Kč';
  }
  var s = n.toFixed(2).replace('.', ',');
  return '€ ' + s;
}

function selectShipping(val) {
  document.querySelectorAll('.checkout-shipping-option').forEach(function(el) {
    el.classList.remove('active');
  });
  var lbl = document.getElementById('lbl-' + val);
  if (lbl) lbl.classList.add('active');

  ['balikovna', 'balikovna_home'].forEach(function(k) {
    var el = document.getElementById('picker-' + k);
    if (el) el.style.display = k === val ? '' : 'none';
  });

  var cost = shippingPrices[val] || 0;
  document.getElementById('summary-shipping').textContent =
    cost === 0 ? labelFree : fmtMoney(cost);
  document.getElementById('summary-total').textContent =
    fmtMoney(cartTotal + cost);
}

/* Balíkovna iframe postMessage listener */
window.addEventListener('message', function(e) {
  if (e.origin !== 'https://b2c.cpost.cz') return;
  var d = e.data;
  if (!d || !d.id) return;

  document.getElementById('pickup-point-id').value   = d.id   || '';
  document.getElementById('pickup-point-name').value = d.name || '';

  var addr = [d.street, d.city, d.zip].filter(Boolean).join(', ');
  document.getElementById('bali-confirmed-name').textContent = d.name || '';
  document.getElementById('bali-confirmed-addr').textContent = addr;
  document.getElementById('bali-confirmed').style.display    = '';
  document.getElementById('bali-iframe-wrap').style.display  = 'none';
});

function clearPickupPoint() {
  document.getElementById('pickup-point-id').value          = '';
  document.getElementById('pickup-point-name').value        = '';
  document.getElementById('bali-confirmed').style.display   = 'none';
  document.getElementById('bali-iframe-wrap').style.display = '';
}
</script>
