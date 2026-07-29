<?php
$msg = '';

/* ── Handle status toggle ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bid    = $_POST['bid']    ?? '';

    if ($action === 'toggle_status' && $bid) {
        foreach ($bookings as &$b) {
            if (($b['id'] ?? '') === $bid) {
                $b['status'] = ($b['status'] ?? 'new') === 'new' ? 'done' : 'new';
                break;
            }
        }
        unset($b);
        file_put_contents(__DIR__ . '/../../data/bookings.json', json_encode($bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: index.php?page=bookings');
        exit;
    }

    if ($action === 'delete' && $bid) {
        $bookings = array_values(array_filter($bookings, fn($b) => ($b['id'] ?? '') !== $bid));
        file_put_contents(__DIR__ . '/../../data/bookings.json', json_encode($bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $msg = $al['book_deleted_msg'];
    }

    if ($action === 'mark_all_done') {
        foreach ($bookings as &$b) $b['status'] = 'done';
        unset($b);
        file_put_contents(__DIR__ . '/../../data/bookings.json', json_encode($bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $msg = $al['book_all_done_msg'];
    }
}

/* ── Sort: newest first ── */
$sorted = array_reverse($bookings);
$filter = $_GET['filter'] ?? 'all';
if ($filter === 'new')  $sorted = array_filter($sorted, fn($b) => ($b['status'] ?? 'new') === 'new');
if ($filter === 'done') $sorted = array_filter($sorted, fn($b) => ($b['status'] ?? 'new') === 'done');

$filters = [
    'all'  => $al['filter_all'],
    'new'  => $al['filter_new'],
    'done' => $al['filter_done'],
];
?>

<?php if ($msg): ?><div class="a-alert a-alert--success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<!-- Filter bar -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.125rem">
  <div style="display:flex;gap:.5rem">
    <?php foreach ($filters as $f => $label): ?>
      <a href="?page=bookings&filter=<?= $f ?>"
         class="a-btn <?= $filter===$f ? 'a-btn--primary' : 'a-btn--outline' ?>">
        <?= htmlspecialchars($label) ?>
        <?php if ($f==='new' && $new_bookings > 0): ?>
          <span style="background:rgba(255,255,255,.3);padding:0 .3rem;border-radius:999px;font-size:.65rem"><?= $new_bookings ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
  <?php if ($new_bookings > 0): ?>
    <form method="post">
      <input type="hidden" name="action" value="mark_all_done">
      <button type="submit" class="a-btn a-btn--success"><?= icon('check') ?> <?= htmlspecialchars($al['btn_mark_all_done']) ?></button>
    </form>
  <?php endif; ?>
</div>

<div class="a-card">
  <?php if (empty($sorted)): ?>
    <div class="a-empty">
      <?= icon('calendar') ?>
      <p><?= $filter !== 'all' ? htmlspecialchars($al['book_empty_filter']) : htmlspecialchars($al['book_empty_all']) ?></p>
    </div>
  <?php else: ?>
    <div class="a-table-wrap">
      <table class="a-table">
        <thead>
          <tr>
            <th><?= htmlspecialchars($al['col_date']) ?></th>
            <th><?= htmlspecialchars($al['col_name']) ?></th>
            <th><?= htmlspecialchars($al['col_contact']) ?></th>
            <th><?= htmlspecialchars($al['col_concern']) ?></th>
            <th><?= htmlspecialchars($al['col_pref_date']) ?></th>
            <th><?= htmlspecialchars($al['col_lang']) ?></th>
            <th><?= htmlspecialchars($al['col_status']) ?></th>
            <th class="a-col-actions"><?= htmlspecialchars($al['col_actions']) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sorted as $b): ?>
            <?php $is_new = ($b['status'] ?? 'new') === 'new'; ?>
            <tr>
              <td style="white-space:nowrap;color:var(--a-muted)">
                <?= htmlspecialchars(substr($b['timestamp'] ?? '—', 0, 16)) ?>
              </td>
              <td>
                <strong><?= htmlspecialchars(($b['vorname'] ?? '') . ' ' . ($b['nachname'] ?? '')) ?></strong>
              </td>
              <td>
                <a href="mailto:<?= htmlspecialchars($b['email'] ?? '') ?>"><?= htmlspecialchars($b['email'] ?? '—') ?></a>
                <?php if (!empty($b['phone'])): ?>
                  <br><a href="tel:<?= htmlspecialchars($b['phone']) ?>" style="color:var(--a-muted)"><?= htmlspecialchars($b['phone']) ?></a>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($b['concern'] ?? '—') ?></td>
              <td style="white-space:nowrap"><?= htmlspecialchars($b['termin'] ?? '—') ?></td>
              <td style="text-transform:uppercase;font-size:.75rem;color:var(--a-muted)"><?= htmlspecialchars($b['lang'] ?? '—') ?></td>
              <td>
                <span class="a-badge <?= $is_new ? 'a-badge--new' : 'a-badge--done' ?>">
                  <?= $is_new ? htmlspecialchars($al['status_new']) : htmlspecialchars($al['status_done']) ?>
                </span>
              </td>
              <td class="a-col-actions">
                <form method="post" style="display:inline">
                  <input type="hidden" name="action" value="toggle_status">
                  <input type="hidden" name="bid"    value="<?= htmlspecialchars($b['id'] ?? '') ?>">
                  <button type="submit" class="a-btn <?= $is_new ? 'a-btn--success' : 'a-btn--outline' ?>"
                          title="<?= $is_new ? htmlspecialchars($al['mark_done_title']) : htmlspecialchars($al['mark_new_title']) ?>">
                    <?= icon('check') ?>
                  </button>
                </form>
                <form method="post" style="display:inline"
                      onsubmit="return confirm(<?= json_encode($al['book_delete_confirm']) ?>)">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="bid"    value="<?= htmlspecialchars($b['id'] ?? '') ?>">
                  <button type="submit" class="a-btn a-btn--danger"><?= icon('trash') ?></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
