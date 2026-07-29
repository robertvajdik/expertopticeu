<?php
require_once __DIR__ . '/../includes/customer_auth.php';
$cur = customer_current();
if (!$cur) {
    header('Location: ?p=login&lang=' . $lang);
    exit;
}

$profile_msg = '';
$profile_errs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $r = customer_update((int)$cur['id'], $_POST);
        if (!empty($r['errors'])) {
            $profile_errs = $r['errors'];
        } else {
            newsletter_set((int)$cur['id'], !empty($_POST['newsletter']));
            $profile_msg = 'ok';
        }
        /* Refresh */
        $cur = customer_current();
    } elseif (isset($_POST['newsletter_toggle'])) {
        newsletter_set((int)$cur['id'], !empty($_POST['newsletter']));
        $profile_msg = 'ok';
    }
}

/* Orders */
try {
    $s = db()->prepare('SELECT id, order_number, created_at, total, status, payment_status
                        FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 50');
    $s->execute([$cur['id']]);
    $orders = $s->fetchAll();
} catch (Exception $e) { $orders = []; }

$order_count = count($orders);
$order_total = array_sum(array_map(fn($o) => (float)$o['total'], $orders));

$is_subscribed = newsletter_is_subscribed((int)$cur['id']);

$initial = mb_strtoupper(mb_substr(trim($cur['name']) ?: $cur['email'], 0, 1));

$status_label = [
    'new'        => $t['ord_status_new']        ?? 'Nová',
    'processing' => $t['ord_status_processing'] ?? 'Zpracovává se',
    'shipped'    => $t['ord_status_shipped']    ?? 'Odesláno',
    'delivered'  => $t['ord_status_delivered']  ?? 'Doručeno',
    'cancelled'  => $t['ord_status_cancelled']  ?? 'Zrušeno',
];
$pay_label = [
    'unpaid'   => $t['ord_pay_unpaid']   ?? 'Nezaplaceno',
    'paid'     => $t['ord_pay_paid']     ?? 'Zaplaceno',
    'refunded' => $t['ord_pay_refunded'] ?? 'Vráceno',
];

$err_msg = [
    'name'             => $t['auth_err_name']        ?? 'Zadejte jméno.',
    'phone'            => $t['auth_err_phone']       ?? 'Neplatné telefonní číslo.',
    'street'           => $t['auth_err_street']      ?? 'Ulice je příliš dlouhá.',
    'city_postal'      => $t['auth_err_city']        ?? 'PSČ a město jsou příliš dlouhé.',
    'password_short'   => $t['auth_err_pw_short']    ?? 'Nové heslo musí mít alespoň 8 znaků.',
    'password_weak'    => $t['auth_err_pw_weak']     ?? 'Heslo musí obsahovat písmeno i číslici.',
    'password_current' => $t['acc_err_pw_current']   ?? 'Aktuální heslo je nesprávné.',
    'db'               => $t['auth_err_db']          ?? 'Uložení se nezdařilo. Zkuste to znovu.',
];
$has_err = fn(string $k) => in_array($k, $profile_errs, true);
?>

<div class="page-wrap acc-wrap">
  <div class="acc-header">
    <div class="acc-avatar"><?= htmlspecialchars($initial) ?></div>
    <div class="acc-header__text">
      <h1 class="acc-name"><?= htmlspecialchars($cur['name']) ?></h1>
      <p class="acc-email"><?= htmlspecialchars($cur['email']) ?></p>
    </div>
    <div class="acc-header__actions">
      <a href="?p=collection&amp;lang=<?= $lang ?>" class="btn btn--secondary btn--sm">
        <i data-lucide="shopping-bag"></i>
        <?= htmlspecialchars($t['acc_shop'] ?? 'Do obchodu') ?>
      </a>
      <a href="?p=logout&amp;lang=<?= $lang ?>" class="btn btn--ghost btn--sm">
        <i data-lucide="log-out"></i>
        <?= htmlspecialchars($t['auth_logout'] ?? 'Odhlásit') ?>
      </a>
    </div>
  </div>

  <?php if ($profile_msg === 'ok'): ?>
    <div class="acc-flash acc-flash--ok"><i data-lucide="check-circle-2"></i><?= htmlspecialchars($t['acc_saved'] ?? 'Údaje uloženy.') ?></div>
  <?php endif; ?>

  <div class="acc-stats">
    <div class="acc-stat">
      <div class="acc-stat__label"><?= htmlspecialchars($t['acc_stat_orders'] ?? 'Objednávky') ?></div>
      <div class="acc-stat__value"><?= $order_count ?></div>
    </div>
    <div class="acc-stat">
      <div class="acc-stat__label"><?= htmlspecialchars($t['acc_stat_spent'] ?? 'Celkem utraceno') ?></div>
      <div class="acc-stat__value"><?= fmt_display($order_total, $lang) ?></div>
    </div>
    <div class="acc-stat">
      <div class="acc-stat__label"><?= htmlspecialchars($t['acc_stat_newsletter'] ?? 'Newsletter') ?></div>
      <div class="acc-stat__value acc-stat__value--sm">
        <?= $is_subscribed
          ? '<span class="acc-badge acc-badge--ok">' . htmlspecialchars($t['acc_news_on']  ?? 'Odebíráte') . '</span>'
          : '<span class="acc-badge acc-badge--off">' . htmlspecialchars($t['acc_news_off'] ?? 'Neodebíráte') . '</span>' ?>
      </div>
    </div>
  </div>

  <div class="acc-grid">

    <!-- Profile edit -->
    <section class="acc-card">
      <h2 class="acc-card__title"><?= htmlspecialchars($t['acc_profile'] ?? 'Osobní údaje') ?></h2>

      <form method="post" class="acc-form">
        <div class="form-field<?= $has_err('name') ? ' form-field--error' : '' ?>">
          <label for="acc-name"><?= htmlspecialchars($t['auth_name'] ?? 'Jméno') ?> *</label>
          <input id="acc-name" type="text" name="name" required minlength="2" maxlength="150"
                 value="<?= htmlspecialchars($_POST['name'] ?? $cur['name']) ?>">
          <?php if ($has_err('name')): ?><p class="form-field__error"><?= htmlspecialchars($err_msg['name']) ?></p><?php endif; ?>
        </div>

        <div class="form-field">
          <label><?= htmlspecialchars($t['auth_email'] ?? 'E-mail') ?></label>
          <input type="email" value="<?= htmlspecialchars($cur['email']) ?>" disabled>
          <p class="auth-hint"><?= htmlspecialchars($t['acc_email_locked'] ?? 'E-mail nelze změnit. Napište nám na kontaktní adresu.') ?></p>
        </div>

        <div class="form-field<?= $has_err('phone') ? ' form-field--error' : '' ?>">
          <label for="acc-phone"><?= htmlspecialchars($t['auth_phone'] ?? 'Telefon') ?></label>
          <input id="acc-phone" type="tel" name="phone" maxlength="25"
                 pattern="^\+?[0-9 \-().]{6,25}$"
                 value="<?= htmlspecialchars($_POST['phone'] ?? $cur['phone'] ?? '') ?>">
          <?php if ($has_err('phone')): ?><p class="form-field__error"><?= htmlspecialchars($err_msg['phone']) ?></p><?php endif; ?>
        </div>

        <div class="form-field<?= $has_err('street') ? ' form-field--error' : '' ?>">
          <label for="acc-street"><?= htmlspecialchars($t['auth_street'] ?? 'Ulice') ?></label>
          <input id="acc-street" type="text" name="street" maxlength="200"
                 value="<?= htmlspecialchars($_POST['street'] ?? $cur['street'] ?? '') ?>">
        </div>

        <div class="form-field<?= $has_err('city_postal') ? ' form-field--error' : '' ?>">
          <label for="acc-city"><?= htmlspecialchars($t['auth_city_postal'] ?? 'PSČ a město') ?></label>
          <input id="acc-city" type="text" name="city_postal" maxlength="120"
                 value="<?= htmlspecialchars($_POST['city_postal'] ?? $cur['city_postal'] ?? '') ?>">
        </div>

        <div class="checkbox-label acc-checkbox">
          <input type="checkbox" id="acc-news" name="newsletter" value="1" <?= $is_subscribed ? 'checked' : '' ?>>
          <label for="acc-news"><?= htmlspecialchars($t['acc_news_label'] ?? 'Chci dostávat newsletter s novinkami a slevami.') ?></label>
        </div>

        <button type="submit" name="update_profile" value="1" class="btn btn--primary btn--block">
          <i data-lucide="save"></i>
          <?= htmlspecialchars($t['acc_save'] ?? 'Uložit změny') ?>
        </button>
      </form>
    </section>

    <!-- Change password -->
    <section class="acc-card">
      <h2 class="acc-card__title"><?= htmlspecialchars($t['acc_pw_title'] ?? 'Změnit heslo') ?></h2>
      <form method="post" class="acc-form">
        <div class="form-field<?= $has_err('password_current') ? ' form-field--error' : '' ?>">
          <label for="acc-pw-cur"><?= htmlspecialchars($t['acc_pw_current'] ?? 'Aktuální heslo') ?></label>
          <input id="acc-pw-cur" type="password" name="password_current" autocomplete="current-password">
          <?php if ($has_err('password_current')): ?><p class="form-field__error"><?= htmlspecialchars($err_msg['password_current']) ?></p><?php endif; ?>
        </div>

        <div class="form-field<?= ($has_err('password_short') || $has_err('password_weak')) ? ' form-field--error' : '' ?>">
          <label for="acc-pw-new"><?= htmlspecialchars($t['acc_pw_new'] ?? 'Nové heslo') ?></label>
          <input id="acc-pw-new" type="password" name="password_new" autocomplete="new-password" minlength="8">
          <?php if ($has_err('password_short')): ?>
            <p class="form-field__error"><?= htmlspecialchars($err_msg['password_short']) ?></p>
          <?php elseif ($has_err('password_weak')): ?>
            <p class="form-field__error"><?= htmlspecialchars($err_msg['password_weak']) ?></p>
          <?php else: ?>
            <p class="auth-hint"><?= htmlspecialchars($t['auth_pw_hint'] ?? 'Minimálně 8 znaků, alespoň jedno písmeno a jedna číslice.') ?></p>
          <?php endif; ?>
        </div>

        <input type="hidden" name="name"        value="<?= htmlspecialchars($cur['name']) ?>">
        <input type="hidden" name="phone"       value="<?= htmlspecialchars($cur['phone'] ?? '') ?>">
        <input type="hidden" name="street"      value="<?= htmlspecialchars($cur['street'] ?? '') ?>">
        <input type="hidden" name="city_postal" value="<?= htmlspecialchars($cur['city_postal'] ?? '') ?>">
        <input type="hidden" name="newsletter"  value="<?= $is_subscribed ? '1' : '' ?>">

        <button type="submit" name="update_profile" value="1" class="btn btn--secondary btn--block">
          <i data-lucide="key-round"></i>
          <?= htmlspecialchars($t['acc_pw_save'] ?? 'Změnit heslo') ?>
        </button>
      </form>
    </section>
  </div>

  <!-- Orders -->
  <section class="acc-orders">
    <h2 class="acc-card__title acc-card__title--section">
      <i data-lucide="package"></i>
      <?= htmlspecialchars($t['acc_orders'] ?? 'Moje objednávky') ?>
    </h2>

    <?php if (!$orders): ?>
      <div class="acc-empty">
        <i data-lucide="shopping-bag"></i>
        <p><?= htmlspecialchars($t['acc_no_orders'] ?? 'Zatím žádné objednávky.') ?></p>
        <a href="?p=collection&amp;lang=<?= $lang ?>" class="btn btn--primary btn--sm">
          <?= htmlspecialchars($t['acc_shop'] ?? 'Do obchodu') ?>
        </a>
      </div>
    <?php else: ?>
      <div class="acc-table-wrap">
        <table class="acc-table">
          <thead>
            <tr>
              <th><?= htmlspecialchars($t['acc_col_no']     ?? 'Číslo') ?></th>
              <th><?= htmlspecialchars($t['acc_col_date']   ?? 'Datum') ?></th>
              <th><?= htmlspecialchars($t['acc_col_total']  ?? 'Celkem') ?></th>
              <th><?= htmlspecialchars($t['acc_col_status'] ?? 'Stav') ?></th>
              <th><?= htmlspecialchars($t['acc_col_pay']    ?? 'Platba') ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td class="acc-table__no"><?= htmlspecialchars($o['order_number']) ?></td>
                <td><?= htmlspecialchars(substr($o['created_at'], 0, 10)) ?></td>
                <td class="acc-table__total"><?= fmt_display((float)$o['total'], $lang) ?></td>
                <td><span class="acc-badge acc-badge--<?= htmlspecialchars($o['status']) ?>"><?= htmlspecialchars($status_label[$o['status']] ?? $o['status']) ?></span></td>
                <td><span class="acc-badge acc-badge--pay-<?= htmlspecialchars($o['payment_status']) ?>"><?= htmlspecialchars($pay_label[$o['payment_status']] ?? $o['payment_status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>
