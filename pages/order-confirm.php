<?php
$order = $_SESSION['last_order'] ?? null;

if (!$order) {
    header('Location: ?p=home&lang=' . $lang);
    exit;
}
unset($_SESSION['last_order']);

$currency  = $t['co_currency'] ?? 'Kč';
$fmt_money = fn(float $n): string => fmt_display($n, $lang);

/* Load bank settings */
$_settings_file = __DIR__ . '/../data/settings.json';
$bank = [];
if (file_exists($_settings_file)) {
    $bank = json_decode(file_get_contents($_settings_file), true) ?: [];
}

/* Build payment reference */
$pay_ref = str_replace('{nr}', $order['number'], $bank['bank_ref'] ?? 'Objednavka {nr}');

/* Build QR payment string (SEPA EPC for EUR, SPD for CZK) */
$qr_data = '';
if (!empty($bank['bank_iban'])) {
    $amount_str = number_format((float)$order['total'], 2, '.', '');
    if ($currency === '€') {
        /* SEPA EPC QR (EPC002-12) */
        $qr_data = implode("\n", [
            'BCD', '002', '1', 'SCT',
            $bank['bank_bic'] ?? '',
            $bank['bank_name'] ?? '',
            $bank['bank_iban'],
            'EUR' . $amount_str,
            'CHAR', $pay_ref,
            $bank['bank_note'] ?? 'expert·optic',
        ]);
    } else {
        /* Czech SPD QR */
        $iban_clean = preg_replace('/\s+/', '', $bank['bank_iban']);
        $qr_data = 'SPD*1.0*ACC:' . $iban_clean;
        if (!empty($bank['bank_bic']))  $qr_data .= '+' . $bank['bank_bic'];
        $qr_data .= '*AM:' . $amount_str . '*CC:CZK';
        $qr_data .= '*MSG:' . $pay_ref;
        if (!empty($bank['bank_note'])) $qr_data .= '*X-VS:' . preg_replace('/\D/', '', $order['number']);
    }
}
?>

<div class="page-wrap" style="max-width:700px;margin:0 auto">

  <div style="text-align:center;padding:2rem 0 1.5rem">
    <div style="width:64px;height:64px;border-radius:50%;background:#d1fae5;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
      <i data-lucide="check-circle" style="width:32px;height:32px;color:#059669"></i>
    </div>
    <h1 style="font-size:2rem;margin:0 0 .4rem"><?= htmlspecialchars($t['conf_title']) ?></h1>
    <p class="lead" style="margin:0;color:var(--ink-500)">
      <?= htmlspecialchars($t['conf_order_no']) ?>: <strong><?= htmlspecialchars($order['number']) ?></strong>
    </p>
  </div>

  <div class="alert alert--success" style="margin-bottom:1.25rem">
    <i data-lucide="mail" style="flex-shrink:0"></i>
    <span><?= htmlspecialchars($t['conf_email_sent']) ?> <strong><?= htmlspecialchars($order['email']) ?></strong></span>
  </div>

  <!-- Items -->
  <div class="frame-tile" style="padding:1.5rem;margin-bottom:1rem">
    <h2 style="font-size:.85rem;margin:0 0 1rem;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400)"><?= htmlspecialchars($t['conf_items']) ?></h2>

    <?php foreach ($order['items'] as $it): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid var(--ink-100)">
        <div>
          <strong><?= htmlspecialchars($it['brand'] . ' ' . $it['name']) ?></strong>
          <span style="color:var(--ink-400);font-size:.85rem"> ×<?= (int)$it['quantity'] ?></span>
        </div>
        <span><?= $fmt_money($it['price'] * $it['quantity']) ?></span>
      </div>
    <?php endforeach; ?>

    <div style="display:flex;justify-content:space-between;padding:.5rem 0;font-size:.9rem;color:var(--ink-500)">
      <span><?= htmlspecialchars($t['co_shipping_row']) ?></span>
      <span><?= $order['shipping_cost'] > 0 ? $fmt_money((float)$order['shipping_cost']) : htmlspecialchars($t['co_free']) ?></span>
    </div>

    <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.15rem;padding-top:.75rem;border-top:2px solid var(--ink-200)">
      <span><?= htmlspecialchars($t['co_total']) ?></span>
      <span><?= $fmt_money((float)$order['total']) ?></span>
    </div>
  </div>

  <!-- Shipping -->
  <div class="frame-tile" style="padding:1.5rem;margin-bottom:1rem">
    <h2 style="font-size:.85rem;margin:0 0 .75rem;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400)"><?= htmlspecialchars($t['conf_delivery']) ?></h2>

    <?php if ($order['shipping'] === 'balikovna'): ?>
      <div style="display:flex;gap:.75rem;align-items:flex-start">
        <i data-lucide="package" style="width:20px;height:20px;flex-shrink:0;color:var(--ink-400);margin-top:.1rem"></i>
        <div>
          <strong><?= htmlspecialchars($t['co_ship_bali_lbl']) ?></strong>
          · <span style="color:var(--clr-primary)"><?= htmlspecialchars($t['co_ship_bali_price']) ?></span><br>
          <?php if (!empty($order['pickup_name'])): ?>
            <span style="color:var(--ink-600)"><?= htmlspecialchars($order['pickup_name']) ?></span><br>
          <?php endif; ?>
          <span style="font-size:.83rem;color:var(--ink-400)"><?= htmlspecialchars($t['conf_bali_note']) ?></span>
        </div>
      </div>

    <?php elseif ($order['shipping'] === 'balikovna_home'): ?>
      <div style="display:flex;gap:.75rem;align-items:flex-start">
        <i data-lucide="truck" style="width:20px;height:20px;flex-shrink:0;color:var(--ink-400);margin-top:.1rem"></i>
        <div>
          <strong><?= htmlspecialchars($t['co_ship_home_lbl']) ?></strong>
          · <span style="color:var(--clr-primary)"><?= htmlspecialchars($t['co_ship_home_price']) ?></span><br>
          <?php if (!empty($order['delivery_address'])): ?>
            <span style="color:var(--ink-600)"><?= htmlspecialchars($order['delivery_address']) ?></span><br>
          <?php endif; ?>
          <span style="font-size:.83rem;color:var(--ink-400)"><?= htmlspecialchars($t['conf_bali_note']) ?></span>
        </div>
      </div>

    <?php else: ?>
      <div style="display:flex;gap:.75rem;align-items:flex-start">
        <i data-lucide="map-pin" style="width:20px;height:20px;flex-shrink:0;color:var(--ink-400);margin-top:.1rem"></i>
        <div>
          <strong><?= htmlspecialchars($t['co_ship_personal_lbl']) ?></strong>
          · <span style="color:#059669"><?= htmlspecialchars($t['co_free']) ?></span><br>
          <span style="color:var(--ink-600)">Expert OPTIC · Hlavní 131, 624 00 Brno-Komín</span><br>
          <span style="font-size:.83rem;color:var(--ink-400)"><?= htmlspecialchars($t['conf_pickup_hours']) ?></span>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Payment -->
  <div class="frame-tile" style="padding:1.5rem;margin-bottom:2rem">
    <h2 style="font-size:.85rem;margin:0 0 .75rem;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400)"><?= htmlspecialchars($t['conf_payment']) ?></h2>
    <div style="display:flex;gap:.75rem;align-items:flex-start">
      <i data-lucide="credit-card" style="width:20px;height:20px;flex-shrink:0;color:var(--ink-400);margin-top:.1rem"></i>
      <div>
        <?= htmlspecialchars($t['conf_payment_info']) ?><br>
        <span style="font-size:.83rem;color:var(--ink-400)"><?= htmlspecialchars($t['conf_payment_sub']) ?></span>
      </div>
    </div>

    <?php if ($qr_data && !empty($bank['bank_iban'])): ?>
      <div class="conf-qr-wrap" style="margin-top:1.25rem">
        <div style="display:flex;gap:1.5rem;align-items:flex-start;flex-wrap:wrap">
          <div id="conf-qr-canvas" class="conf-qr-canvas"></div>
          <div style="flex:1;min-width:180px">
            <p style="margin:0 0 .25rem;font-size:.8125rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400)"><?= htmlspecialchars($t['conf_bank_transfer']) ?></p>
            <?php if (!empty($bank['bank_name'])): ?>
              <p style="margin:0 0 .125rem"><strong><?= htmlspecialchars($bank['bank_name']) ?></strong></p>
            <?php endif; ?>
            <p style="margin:0 0 .125rem;font-family:var(--font-mono,monospace);font-size:.875rem"><?= htmlspecialchars($bank['bank_iban']) ?></p>
            <?php if (!empty($bank['bank_bic'])): ?>
              <p style="margin:0 0 .125rem;font-size:.875rem;color:var(--ink-500)"><?= htmlspecialchars($bank['bank_bic']) ?></p>
            <?php endif; ?>
            <p style="margin:.5rem 0 0;font-size:.875rem">
              <?= htmlspecialchars($t['conf_amount']) ?>: <strong><?= $fmt_money((float)$order['total']) ?></strong>
            </p>
            <p style="margin:.125rem 0 0;font-size:.875rem">
              <?= htmlspecialchars($t['conf_ref']) ?>: <strong><?= htmlspecialchars($pay_ref) ?></strong>
            </p>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
    <a href="?p=collection&amp;lang=<?= $lang ?>" class="btn btn--secondary">
      <i data-lucide="glasses"></i> <?= htmlspecialchars($t['conf_continue']) ?>
    </a>
    <a href="?p=booking&amp;lang=<?= $lang ?>" class="btn btn--primary">
      <i data-lucide="calendar"></i> <?= htmlspecialchars($t['conf_booking']) ?>
    </a>
  </div>

</div>

<?php if ($qr_data): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSFMFyMPn+Ji7F9h7VkO0K6agNFqKNwne/w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
var qrEl = document.getElementById('conf-qr-canvas');
if (qrEl) {
  new QRCode(qrEl, {
    text:         <?= json_encode($qr_data) ?>,
    width:        160,
    height:       160,
    colorDark:    '#1a1a2e',
    colorLight:   '#ffffff',
    correctLevel: QRCode.CorrectLevel.M
  });
}
</script>
<?php endif; ?>

