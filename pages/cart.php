<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/customer_auth.php';

$voucher_msg = '';

/* ── Actions ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action']  ?? '';
    $item_id = (int)($_POST['item_id'] ?? 0);

    if ($action === 'update' && $item_id) {
        cart_update_qty($item_id, (int)($_POST['qty'] ?? 1));
    } elseif ($action === 'remove' && $item_id) {
        cart_remove($item_id);
    } elseif ($action === 'apply_voucher') {
        $code = strtoupper(trim($_POST['voucher_code'] ?? ''));
        $subtotal = cart_total(cart_items());
        $reason   = null;
        if (voucher_lookup($code, $subtotal, $reason)) {
            voucher_apply($code);
            $voucher_msg = 'ok';
        } else {
            voucher_clear();
            $voucher_msg = $reason ?: 'invalid';
        }
    } elseif ($action === 'remove_voucher') {
        voucher_clear();
    }
    if ($action !== 'apply_voucher' && $action !== 'remove_voucher') {
        $_SESSION['cart_count'] = cart_count();
        header('Location: ?p=cart&lang=' . $lang);
        exit;
    }
    if ($action === 'remove_voucher') {
        header('Location: ?p=cart&lang=' . $lang);
        exit;
    }
}

$items    = cart_items();
$subtotal = cart_total($items);

/* Re-validate any applied voucher against the current cart. */
$voucher    = null;
$discount   = 0.0;
$vc = voucher_current_code();
if ($vc) {
    $r = null;
    $v = voucher_lookup($vc, $subtotal, $r);
    if ($v) {
        $voucher  = $v;
        $discount = voucher_discount_eur($v, $subtotal);
    } else {
        voucher_clear();
    }
}
$total     = max(0, $subtotal - $discount);
$fmt_money = fn(float $n): string => fmt_display($n, $lang);

$voucher_messages = [
    'ok'       => $t['vou_msg_ok']       ?? 'Voucher aplikován.',
    'notfound' => $t['vou_msg_notfound'] ?? 'Neplatný kód.',
    'inactive' => $t['vou_msg_inactive'] ?? 'Voucher není aktivní.',
    'expired'  => $t['vou_msg_expired']  ?? 'Platnost voucheru vypršela.',
    'used_up'  => $t['vou_msg_used_up']  ?? 'Voucher byl již plně použit.',
    'min_order'=> $t['vou_msg_min_ord']  ?? 'Voucher vyžaduje vyšší minimální objednávku.',
    'empty'    => $t['vou_msg_empty']    ?? 'Zadejte kód.',
    'db'       => $t['vou_msg_error']    ?? 'Chyba. Zkuste to znovu.',
];
?>

<div class="page-wrap">
  <a href="?p=collection&amp;lang=<?= $lang ?>" class="back-link">
    <i data-lucide="arrow-left"></i>
    <?= htmlspecialchars($t['prod_back']) ?>
  </a>

  <h1 class="cart-page-title"><?= htmlspecialchars($t['cart_title']) ?></h1>

  <?php if (!$customer && !empty($items)): ?>
    <div class="cart-login-prompt">
      <div class="cart-login-prompt__icon"><i data-lucide="user-round"></i></div>
      <div class="cart-login-prompt__text">
        <strong><?= htmlspecialchars($t['cart_login_title'] ?? 'Máte u nás účet?') ?></strong>
        <span><?= htmlspecialchars($t['cart_login_lead'] ?? 'Přihlaste se pro rychlejší pokladnu a přehled objednávek.') ?></span>
      </div>
      <div class="cart-login-prompt__actions">
        <a href="?p=login&amp;next=cart&amp;lang=<?= $lang ?>" class="btn btn--primary btn--sm">
          <i data-lucide="log-in"></i>
          <?= htmlspecialchars($t['cart_login_btn'] ?? 'Přihlásit') ?>
        </a>
        <a href="?p=register&amp;lang=<?= $lang ?>" class="btn btn--secondary btn--sm">
          <?= htmlspecialchars($t['cart_register_btn'] ?? 'Registrovat') ?>
        </a>
      </div>
    </div>
  <?php endif; ?>

  <?php if (empty($items)): ?>
    <div class="alert alert--navy cart-empty">
      <i data-lucide="shopping-bag"></i>
      <div>
        <strong><?= htmlspecialchars($t['cart_empty']) ?></strong><br>
        <a href="?p=collection&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['cart_browse']) ?></a>
      </div>
    </div>

  <?php else: ?>
    <div class="cart-layout">

      <!-- Items list -->
      <div class="cart-items">
        <?php foreach ($items as $item): ?>
          <div class="cart-item">

            <!-- Thumbnail -->
            <div class="cart-item__thumb" style="background:<?= htmlspecialchars($item['color']) ?>1a">
              <?php if (!empty($item['img'])): ?>
                <img src="<?= htmlspecialchars($item['img']) ?>" alt="">
              <?php else: ?>
                <i data-lucide="glasses" style="color:<?= htmlspecialchars($item['color']) ?>"></i>
              <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="cart-item__info">
              <p class="cart-item__brand"><?= htmlspecialchars($item['brand']) ?></p>
              <p class="cart-item__name"><?= htmlspecialchars($item['name']) ?></p>
              <p class="cart-item__cat"><?= htmlspecialchars($item['cat']) ?></p>
              <p class="cart-item__price"><?= $fmt_money($item['price'] * $item['quantity']) ?></p>
            </div>

            <!-- Qty + remove -->
            <div class="cart-item__controls">
              <form method="post" class="cart-qty">
                <input type="hidden" name="action"  value="update">
                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                <button type="submit" name="qty" value="<?= $item['quantity'] - 1 ?>"
                        class="btn btn--secondary cart-qty__btn" aria-label="−">−</button>
                <span class="cart-qty__count"><?= $item['quantity'] ?></span>
                <button type="submit" name="qty" value="<?= $item['quantity'] + 1 ?>"
                        class="btn btn--secondary cart-qty__btn" aria-label="+">+</button>
              </form>
              <form method="post">
                <input type="hidden" name="action"  value="remove">
                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                <button type="submit" class="btn btn--secondary cart-remove">
                  <i data-lucide="trash-2"></i>
                  <?= htmlspecialchars($t['cart_remove']) ?>
                </button>
              </form>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

      <!-- Summary -->
      <div class="card cart-summary">
        <h2 class="cart-summary__heading"><?= htmlspecialchars($t['cart_summary']) ?></h2>

        <?php foreach ($items as $item): ?>
          <div class="cart-summary__line">
            <span><?= htmlspecialchars($item['brand'] . ' ' . $item['name']) ?> ×<?= $item['quantity'] ?></span>
            <span><?= $fmt_money($item['price'] * $item['quantity']) ?></span>
          </div>
        <?php endforeach; ?>

        <hr class="cart-summary__divider">

        <!-- Voucher -->
        <div class="cart-summary__line">
          <?php if ($voucher): ?>
            <span><?= htmlspecialchars($t['cart_voucher'] ?? 'Voucher') ?>
                  <strong><?= htmlspecialchars($voucher['code']) ?></strong></span>
            <span style="color:#16a34a">− <?= $fmt_money($discount) ?></span>
          <?php else: ?>
            <span><?= htmlspecialchars($t['cart_voucher'] ?? 'Voucher') ?></span>
            <span>—</span>
          <?php endif; ?>
        </div>
        <form method="post" style="display:flex;gap:.5rem;margin:.5rem 0 .75rem">
          <?php if ($voucher): ?>
            <input type="hidden" name="action" value="remove_voucher">
            <button type="submit" class="btn btn--secondary" style="flex:1">
              <?= htmlspecialchars($t['cart_voucher_remove'] ?? 'Odebrat voucher') ?>
            </button>
          <?php else: ?>
            <input type="hidden" name="action" value="apply_voucher">
            <input type="text" name="voucher_code" placeholder="<?= htmlspecialchars($t['cart_voucher_ph'] ?? 'Kód voucheru') ?>"
                   style="flex:1;text-transform:uppercase;padding:.5rem;border:1px solid var(--border,#e5e7eb);border-radius:6px">
            <button type="submit" class="btn btn--secondary">
              <?= htmlspecialchars($t['cart_voucher_apply'] ?? 'Použít') ?>
            </button>
          <?php endif; ?>
        </form>
        <?php if ($voucher_msg && $voucher_msg !== 'ok'): ?>
          <p style="color:#dc2626;font-size:.85rem;margin:0 0 .75rem"><?= htmlspecialchars($voucher_messages[$voucher_msg] ?? $voucher_msg) ?></p>
        <?php elseif ($voucher_msg === 'ok'): ?>
          <p style="color:#16a34a;font-size:.85rem;margin:0 0 .75rem"><?= htmlspecialchars($voucher_messages['ok']) ?></p>
        <?php endif; ?>

        <div class="cart-summary__total">
          <span><?= htmlspecialchars($t['cart_total']) ?></span>
          <span><?= $fmt_money($total) ?></span>
        </div>
        <p class="cart-summary__vat"><?= htmlspecialchars($t['cart_vat']) ?></p>

        <div class="cart-summary__actions">
          <a href="?p=checkout&amp;lang=<?= $lang ?>" class="btn btn--primary btn--lg btn--block">
            <i data-lucide="arrow-right"></i>
            <?= htmlspecialchars($t['cart_checkout']) ?>
          </a>
          <a href="?p=collection&amp;lang=<?= $lang ?>" class="btn btn--secondary btn--block">
            <?= htmlspecialchars($t['cart_continue']) ?>
          </a>
        </div>
      </div>

    </div>
  <?php endif; ?>
</div>
