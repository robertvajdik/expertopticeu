<?php
require_once __DIR__ . '/../includes/customer_auth.php';
$cur = customer_current();
if (!$cur) {
    header('Location: ?p=login&lang=' . $lang);
    exit;
}

try {
    $s = db()->prepare('SELECT id, order_number, created_at, total, status, payment_status
                        FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 50');
    $s->execute([$cur['id']]);
    $orders = $s->fetchAll();
} catch (Exception $e) { $orders = []; }
?>

<div class="page-wrap">
  <h1 class="cart-page-title"><?= htmlspecialchars($t['acc_title'] ?? 'Můj účet') ?></h1>

  <div class="card" style="padding:1.5rem;margin-bottom:1.5rem">
    <p style="margin:0 0 .5rem">
      <strong><?= htmlspecialchars($cur['name']) ?></strong>
      · <?= htmlspecialchars($cur['email']) ?>
    </p>
    <?php if ($cur['phone']):       ?><p style="margin:.25rem 0"><?= htmlspecialchars($cur['phone']) ?></p><?php endif; ?>
    <?php if ($cur['street'] || $cur['city_postal']): ?>
      <p style="margin:.25rem 0"><?= htmlspecialchars(trim(($cur['street'] ?? '') . ', ' . ($cur['city_postal'] ?? ''), ', ')) ?></p>
    <?php endif; ?>
    <p style="margin-top:1rem">
      <a href="?p=logout&amp;lang=<?= $lang ?>" class="btn btn--secondary"><?= htmlspecialchars($t['auth_logout'] ?? 'Odhlásit') ?></a>
    </p>
  </div>

  <h2 style="margin:0 0 .75rem"><?= htmlspecialchars($t['acc_orders'] ?? 'Moje objednávky') ?></h2>
  <?php if (!$orders): ?>
    <p style="color:var(--ink-500)"><?= htmlspecialchars($t['acc_no_orders'] ?? 'Zatím žádné objednávky.') ?></p>
  <?php else: ?>
    <div class="card" style="padding:0;overflow:hidden">
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr style="text-align:left;background:var(--surface-100)">
            <th style="padding:.75rem 1rem"><?= htmlspecialchars($t['acc_col_no']     ?? 'Č.') ?></th>
            <th style="padding:.75rem 1rem"><?= htmlspecialchars($t['acc_col_date']   ?? 'Datum') ?></th>
            <th style="padding:.75rem 1rem"><?= htmlspecialchars($t['acc_col_total']  ?? 'Celkem') ?></th>
            <th style="padding:.75rem 1rem"><?= htmlspecialchars($t['acc_col_status'] ?? 'Stav') ?></th>
            <th style="padding:.75rem 1rem"><?= htmlspecialchars($t['acc_col_pay']    ?? 'Platba') ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr style="border-top:1px solid var(--border)">
              <td style="padding:.75rem 1rem;font-family:monospace"><?= htmlspecialchars($o['order_number']) ?></td>
              <td style="padding:.75rem 1rem"><?= htmlspecialchars(substr($o['created_at'], 0, 10)) ?></td>
              <td style="padding:.75rem 1rem"><?= fmt_display((float)$o['total'], $lang) ?></td>
              <td style="padding:.75rem 1rem"><?= htmlspecialchars($o['status']) ?></td>
              <td style="padding:.75rem 1rem"><?= htmlspecialchars($o['payment_status']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
