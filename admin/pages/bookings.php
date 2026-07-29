<?php
$msg = '';

/* ── iCal (.ics) export ──
   ?export=ics           — all bookings (respects &filter=)
   ?export=ics&id=<bid>  — single booking */
if (($_GET['export'] ?? '') === 'ics') {
    while (ob_get_level() > 0) ob_end_clean();

    $ics_escape = fn(string $s): string => str_replace(
        ['\\', ';', ',', "\r\n", "\n", "\r"],
        ['\\\\', '\\;', '\\,', '\\n', '\\n', ''],
        $s
    );
    $ics_fold = function (string $line): string {
        if (strlen($line) <= 74) return $line;
        $out = ''; $len = strlen($line);
        for ($i = 0; $i < $len; $i += 73) {
            $out .= ($i > 0 ? "\r\n " : '') . substr($line, $i, 73);
        }
        return $out;
    };

    $exp_id     = $_GET['id']     ?? '';
    $exp_filter = $_GET['filter'] ?? 'all';
    $items = $bookings;
    if ($exp_id !== '') {
        $items = array_filter($items, fn($b) => ($b['id'] ?? '') === $exp_id);
    } else {
        if ($exp_filter === 'new')  $items = array_filter($items, fn($b) => ($b['status'] ?? 'new') === 'new');
        if ($exp_filter === 'done') $items = array_filter($items, fn($b) => ($b['status'] ?? 'new') === 'done');
    }

    $now  = gmdate('Ymd\THis\Z');
    $host = $_SERVER['HTTP_HOST'] ?? 'exportoptic.eu';

    $lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//expert-optic//Bookings//EN',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'X-WR-CALNAME:expert·optic — ' . ($al['title_bookings'] ?? 'Termíny'),
    ];

    foreach ($items as $b) {
        $termin = trim($b['termin'] ?? '');
        if ($termin === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $termin)) continue;

        $date_ymd = str_replace('-', '', $termin);
        $next_ymd = date('Ymd', strtotime($termin . ' +1 day'));

        $name    = trim(($b['vorname'] ?? '') . ' ' . ($b['nachname'] ?? ''));
        $concern = trim($b['concern'] ?? '');
        $email   = trim($b['email']   ?? '');
        $phone   = trim($b['phone']   ?? '');
        $blang   = strtoupper($b['lang'] ?? '');

        $summary = 'Termín: ' . ($name !== '' ? $name : 'Zákazník') . ($concern !== '' ? ' — ' . $concern : '');
        $desc_parts = array_filter([
            $name,
            $email !== '' ? 'E-mail: ' . $email : '',
            $phone !== '' ? 'Telefon: ' . $phone : '',
            $concern !== '' ? 'Zájem: ' . $concern : '',
            $blang !== '' ? 'Jazyk: ' . $blang : '',
        ]);
        $desc = implode('\\n', array_map($ics_escape, $desc_parts));
        $uid  = ($b['id'] ?? bin2hex(random_bytes(8))) . '@' . $host;
        $stat = ($b['status'] ?? 'new') === 'new' ? 'TENTATIVE' : 'CONFIRMED';

        $lines[] = 'BEGIN:VEVENT';
        $lines[] = 'UID:'   . $uid;
        $lines[] = 'DTSTAMP:' . $now;
        $lines[] = 'DTSTART;VALUE=DATE:' . $date_ymd;
        $lines[] = 'DTEND;VALUE=DATE:'   . $next_ymd;
        $lines[] = 'SUMMARY:' . $ics_escape($summary);
        $lines[] = 'DESCRIPTION:' . $desc;
        if ($email !== '') {
            $lines[] = 'ATTENDEE;CN=' . $ics_escape($name !== '' ? $name : $email) . ':mailto:' . $email;
        }
        $lines[] = 'LOCATION:' . $ics_escape('expert·optic — Hlavní 131, 624 00 Brno-Komín');
        $lines[] = 'STATUS:' . $stat;
        $lines[] = 'END:VEVENT';
    }

    $lines[] = 'END:VCALENDAR';

    $filename = $exp_id !== ''
        ? 'booking-' . preg_replace('/[^A-Za-z0-9]/', '', $exp_id) . '.ics'
        : 'bookings-' . date('Ymd') . '.ics';

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, max-age=0');
    echo implode("\r\n", array_map($ics_fold, $lines)) . "\r\n";
    exit;
}

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
  <div style="display:flex;gap:.5rem;flex-wrap:wrap">
    <a href="?page=bookings&export=ics&filter=<?= htmlspecialchars($filter) ?>"
       class="a-btn a-btn--outline" title="<?= htmlspecialchars($al['btn_export_ics'] ?? 'Export .ics') ?>">
      <?= icon('calendar') ?> <?= htmlspecialchars($al['btn_export_ics'] ?? 'Export .ics') ?>
    </a>
    <?php if ($new_bookings > 0): ?>
      <form method="post">
        <input type="hidden" name="action" value="mark_all_done">
        <button type="submit" class="a-btn a-btn--success"><?= icon('check') ?> <?= htmlspecialchars($al['btn_mark_all_done']) ?></button>
      </form>
    <?php endif; ?>
  </div>
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
                <a href="?page=bookings&export=ics&id=<?= urlencode($b['id'] ?? '') ?>"
                   class="a-btn a-btn--outline"
                   title="<?= htmlspecialchars($al['btn_export_ics_row'] ?? 'Přidat do kalendáře') ?>">
                  <?= icon('calendar') ?>
                </a>
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
