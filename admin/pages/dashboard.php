<?php
$total_products  = count($products);
$total_bookings  = count($bookings);
$done_bookings   = count(array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'done'));
$recent_bookings = array_slice(array_reverse($bookings), 0, 5);
?>

<!-- Stat cards -->
<div class="a-stats">
  <div class="a-stat-card">
    <div class="a-stat-icon a-stat-icon--blue"><?= icon('glasses') ?></div>
    <div>
      <div class="a-stat-value"><?= $total_products ?></div>
      <div class="a-stat-label"><?= htmlspecialchars($al['stat_products']) ?></div>
    </div>
  </div>
  <div class="a-stat-card">
    <div class="a-stat-icon a-stat-icon--accent"><?= icon('calendar') ?></div>
    <div>
      <div class="a-stat-value"><?= $total_bookings ?></div>
      <div class="a-stat-label"><?= htmlspecialchars($al['stat_all_bookings']) ?></div>
    </div>
  </div>
  <div class="a-stat-card">
    <div class="a-stat-icon a-stat-icon--amber"><?= icon('users') ?></div>
    <div>
      <div class="a-stat-value"><?= $new_bookings ?></div>
      <div class="a-stat-label"><?= htmlspecialchars($al['stat_new_bookings']) ?></div>
    </div>
  </div>
  <div class="a-stat-card">
    <div class="a-stat-icon a-stat-icon--green"><?= icon('check') ?></div>
    <div>
      <div class="a-stat-value"><?= $done_bookings ?></div>
      <div class="a-stat-label"><?= htmlspecialchars($al['stat_done']) ?></div>
    </div>
  </div>
</div>

<!-- Dashboard grid -->
<div class="a-dash-grid">

  <!-- Recent bookings -->
  <div class="a-card">
    <div class="a-card-head">
      <h2><?= htmlspecialchars($al['dash_recent']) ?></h2>
      <a href="index.php?page=bookings" class="a-btn a-btn--outline"><?= htmlspecialchars($al['dash_view_all']) ?></a>
    </div>
    <?php if (empty($recent_bookings)): ?>
      <div class="a-empty">
        <?= icon('calendar') ?>
        <p><?= htmlspecialchars($al['dash_no_bookings']) ?></p>
      </div>
    <?php else: ?>
      <div class="a-table-wrap">
        <table class="a-table">
          <thead>
            <tr>
              <th><?= htmlspecialchars($al['col_name']) ?></th>
              <th><?= htmlspecialchars($al['col_concern']) ?></th>
              <th><?= htmlspecialchars($al['col_pref_date']) ?></th>
              <th><?= htmlspecialchars($al['col_status']) ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_bookings as $b): ?>
              <tr>
                <td><strong><?= htmlspecialchars($b['vorname'] . ' ' . $b['nachname']) ?></strong><br>
                    <small style="color:var(--a-muted)"><?= htmlspecialchars($b['email'] ?? '') ?></small></td>
                <td><?= htmlspecialchars($b['concern'] ?? '—') ?></td>
                <td><?= htmlspecialchars($b['termin'] ?? '—') ?></td>
                <td>
                  <span class="a-badge <?= ($b['status'] ?? 'new') === 'new' ? 'a-badge--new' : 'a-badge--done' ?>">
                    <?= ($b['status'] ?? 'new') === 'new' ? htmlspecialchars($al['status_new']) : htmlspecialchars($al['status_done']) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Quick links -->
  <div class="a-card">
    <div class="a-card-head">
      <h2><?= htmlspecialchars($al['dash_quick']) ?></h2>
    </div>
    <div class="a-card-body" style="display:flex;flex-direction:column;gap:.625rem">
      <a href="index.php?page=products" class="a-btn a-btn--outline" style="justify-content:flex-start;padding:.625rem .875rem">
        <?= icon('glasses') ?> <?= htmlspecialchars($al['dash_manage_products']) ?>
      </a>
      <a href="index.php?page=bookings" class="a-btn a-btn--outline" style="justify-content:flex-start;padding:.625rem .875rem">
        <?= icon('calendar') ?> <?= htmlspecialchars($al['dash_view_bookings']) ?>
      </a>
      <a href="../index.php?p=booking" target="_blank" class="a-btn a-btn--outline" style="justify-content:flex-start;padding:.625rem .875rem">
        <?= icon('external') ?> <?= htmlspecialchars($al['dash_booking_form']) ?>
      </a>
      <a href="../index.php?p=brno&lang=cz" target="_blank" class="a-btn a-btn--outline" style="justify-content:flex-start;padding:.625rem .875rem">
        <?= icon('external') ?> <?= htmlspecialchars($al['dash_studio_brno']) ?>
      </a>
    </div>

    <div style="padding:1rem 1.375rem;border-top:1px solid var(--a-border);margin-top:.5rem">
      <p style="font-size:.75rem;color:var(--a-muted);margin:0 0 .5rem;font-weight:600"><?= htmlspecialchars($al['dash_locations']) ?></p>
      <div style="font-size:.8125rem;color:var(--a-text);display:flex;flex-direction:column;gap:.25rem">
        <span>🇨🇿 Hlavní 131, 624 00 Brno-Komín</span>
      </div>
    </div>
  </div>

</div>
