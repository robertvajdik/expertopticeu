<?php
$msg   = '';
$error = '';

/* Ensure table exists */
try {
    db()->query('SELECT 1 FROM newsletter_subscribers LIMIT 1');
    $schema_ok = true;
} catch (Exception $e) {
    $schema_ok = false;
}

/* ── CSV export ── */
if ($schema_ok && ($_GET['action'] ?? '') === 'export') {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=newsletter_' . date('Y-m-d') . '.csv');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // BOM for Excel
    fputcsv($out, ['id', 'email', 'name', 'lang', 'active', 'created_at'], ';');
    $rows = db()->query('SELECT id, email, name, lang, active, created_at FROM newsletter_subscribers ORDER BY created_at DESC')->fetchAll();
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'], $r['email'], $r['name'], $r['lang'], $r['active'], $r['created_at']], ';');
    }
    fclose($out);
    exit;
}

/* ── POST actions ── */
if ($schema_ok && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle') {
        $tid = (int)($_POST['toggle_id'] ?? 0);
        if ($tid) {
            try {
                db()->prepare('UPDATE newsletter_subscribers SET active = NOT active WHERE id = ?')
                    ->execute([$tid]);
                $msg = $al['news_toggled_msg'] ?? 'Stav aktualizován.';
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
    }

    if ($action === 'delete') {
        $did = (int)($_POST['del_id'] ?? 0);
        if ($did) {
            try {
                db()->prepare('DELETE FROM newsletter_subscribers WHERE id = ?')->execute([$did]);
                $msg = $al['news_deleted_msg'] ?? 'Odběratel odstraněn.';
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
    }

    if ($action === 'add') {
        $em = strtolower(trim($_POST['email'] ?? ''));
        $nm = trim($_POST['name']  ?? '');
        $ln = in_array($_POST['lang'] ?? 'cz', ['cz','en','at'], true) ? $_POST['lang'] : 'cz';
        if (!filter_var($em, FILTER_VALIDATE_EMAIL)) {
            $error = $al['news_err_email'] ?? 'Neplatný e-mail.';
        } else {
            try {
                db()->prepare('INSERT INTO newsletter_subscribers (email, name, lang, active)
                               VALUES (?, ?, ?, 1)
                               ON DUPLICATE KEY UPDATE active = 1, name = COALESCE(VALUES(name), name), lang = VALUES(lang)')
                    ->execute([$em, $nm ?: null, $ln]);
                $msg = $al['news_added_msg'] ?? 'Odběratel přidán.';
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
    }
}

/* ── Filters ── */
$q      = trim($_GET['q']      ?? '');
$fLang  = $_GET['flang']  ?? '';
$fState = $_GET['fstate'] ?? '';

$where  = [];
$params = [];
if ($q !== '') {
    $where[] = '(email LIKE ? OR name LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if (in_array($fLang, ['cz','en','at'], true)) {
    $where[] = 'lang = ?';
    $params[] = $fLang;
}
if ($fState === 'active')   { $where[] = 'active = 1'; }
if ($fState === 'inactive') { $where[] = 'active = 0'; }

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* ── Load ── */
$rows = [];
$stats = ['total' => 0, 'active' => 0, 'cz' => 0, 'en' => 0, 'at' => 0];
if ($schema_ok) {
    $stmt = db()->prepare("SELECT id, email, name, lang, active, created_at
                           FROM newsletter_subscribers
                           $whereSql
                           ORDER BY created_at DESC");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $stats['total']  = (int)db()->query('SELECT COUNT(*) FROM newsletter_subscribers')->fetchColumn();
    $stats['active'] = (int)db()->query('SELECT COUNT(*) FROM newsletter_subscribers WHERE active = 1')->fetchColumn();
    foreach (['cz','en','at'] as $lg) {
        $s = db()->prepare('SELECT COUNT(*) FROM newsletter_subscribers WHERE lang = ? AND active = 1');
        $s->execute([$lg]);
        $stats[$lg] = (int)$s->fetchColumn();
    }
}
?>

<?php if (!$schema_ok): ?>
  <div class="a-alert a-alert--error">
    <?= htmlspecialchars($al['news_err_schema'] ?? 'Tabulka newsletter_subscribers neexistuje. Spusťte data/migrate.sql.') ?>
  </div>
<?php else: ?>

  <?php if ($msg):   ?><div class="a-alert a-alert--success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="a-alert a-alert--error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <?php $inactive = max(0, $stats['total'] - $stats['active']); ?>

  <!-- Stats -->
  <div class="a-stats">
    <div class="a-stat-card">
      <div class="a-stat-icon a-stat-icon--blue"><?= icon('users') ?></div>
      <div>
        <div class="a-stat-value"><?= $stats['total'] ?></div>
        <div class="a-stat-label"><?= htmlspecialchars($al['news_stat_total'] ?? 'Celkem') ?></div>
      </div>
    </div>
    <div class="a-stat-card">
      <div class="a-stat-icon a-stat-icon--green"><?= icon('check') ?></div>
      <div>
        <div class="a-stat-value"><?= $stats['active'] ?></div>
        <div class="a-stat-label"><?= htmlspecialchars($al['news_stat_active'] ?? 'Aktivní') ?></div>
      </div>
    </div>
    <div class="a-stat-card">
      <div class="a-stat-icon a-stat-icon--amber"><?= icon('x') ?></div>
      <div>
        <div class="a-stat-value"><?= $inactive ?></div>
        <div class="a-stat-label"><?= htmlspecialchars($al['news_stat_inactive'] ?? 'Odhlášeno') ?></div>
      </div>
    </div>
    <div class="a-stat-card">
      <div class="a-stat-icon a-stat-icon--accent"><?= icon('globe') ?: icon('mail') ?></div>
      <div style="width:100%">
        <div class="a-stat-label" style="margin:0 0 .375rem"><?= htmlspecialchars($al['news_stat_by_lang'] ?? 'Aktivní podle jazyka') ?></div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
          <span class="a-badge a-badge--cat" style="gap:.375rem"><strong>CZ</strong> <?= $stats['cz'] ?></span>
          <span class="a-badge a-badge--cat" style="gap:.375rem"><strong>EN</strong> <?= $stats['en'] ?></span>
          <span class="a-badge a-badge--cat" style="gap:.375rem"><strong>AT</strong> <?= $stats['at'] ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Add form (collapsed by default; opens via "Přidat odběratele") -->
  <div class="a-card" id="news-form-card" style="<?= $error && ($_POST['action'] ?? '') === 'add' ? '' : 'display:none' ?>;margin-bottom:1.25rem">
    <div class="a-card-head">
      <h2><?= htmlspecialchars($al['news_new_title'] ?? 'Přidat odběratele') ?></h2>
      <button type="button" class="a-btn a-btn--outline" onclick="hideNewsForm()" aria-label="Zavřít">×</button>
    </div>
    <div class="a-card-body">
      <form method="post" class="a-form">
        <input type="hidden" name="action" value="add">
        <div class="a-form-row" style="grid-template-columns:1fr 1fr 120px">
          <div class="a-field">
            <label><?= htmlspecialchars($al['news_col_email'] ?? 'E-mail') ?></label>
            <input type="email" name="email" required placeholder="jmeno@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
          <div class="a-field">
            <label><?= htmlspecialchars($al['news_col_name'] ?? 'Jméno') ?></label>
            <input type="text" name="name" placeholder="Jan Novák"
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
          </div>
          <div class="a-field">
            <label><?= htmlspecialchars($al['news_col_lang'] ?? 'Jazyk') ?></label>
            <select name="lang">
              <option value="cz">CZ</option>
              <option value="en">EN</option>
              <option value="at">AT</option>
            </select>
          </div>
        </div>
        <div class="a-form-actions">
          <button type="submit" class="a-btn a-btn--primary">
            <?= icon('check') ?> <?= htmlspecialchars($al['news_save'] ?? 'Přidat') ?>
          </button>
          <button type="button" class="a-btn a-btn--outline" onclick="hideNewsForm()">
            <?= htmlspecialchars($al['btn_cancel'] ?? 'Zrušit') ?>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Subscribers card: filter bar in card head, table below -->
  <div class="a-card">
    <div class="a-card-head" style="flex-wrap:wrap;gap:.75rem">
      <h2 style="margin-right:auto">
        <?= htmlspecialchars($al['news_all_title'] ?? 'Odběratelé') ?>
        <span style="color:var(--a-muted);font-weight:500">(<?= count($rows) ?>)</span>
      </h2>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <a href="?page=newsletter&amp;action=export" class="a-btn a-btn--outline">
          <?= icon('external') ?> <?= htmlspecialchars($al['news_export'] ?? 'Export CSV') ?>
        </a>
        <button type="button" class="a-btn a-btn--primary" onclick="showNewsForm()">
          <?= icon('plus') ?> <?= htmlspecialchars($al['news_new_title'] ?? 'Přidat odběratele') ?>
        </button>
      </div>
    </div>

    <div class="a-card-body" style="padding:.875rem 1rem;border-bottom:1px solid var(--a-border);background:var(--a-bg-soft,#fafafa)">
      <form method="get" style="display:flex;gap:.625rem;flex-wrap:wrap;align-items:center;margin:0">
        <input type="hidden" name="page" value="newsletter">
        <input type="search" name="q" value="<?= htmlspecialchars($q) ?>"
               placeholder="<?= htmlspecialchars($al['news_search'] ?? 'Hledat e-mail nebo jméno…') ?>"
               style="flex:1;min-width:200px;padding:.5rem .75rem">
        <select name="flang" style="padding:.5rem .5rem;max-width:110px">
          <option value=""><?= htmlspecialchars($al['news_all'] ?? 'Všechny jazyky') ?></option>
          <option value="cz" <?= $fLang==='cz'?'selected':'' ?>>CZ</option>
          <option value="en" <?= $fLang==='en'?'selected':'' ?>>EN</option>
          <option value="at" <?= $fLang==='at'?'selected':'' ?>>AT</option>
        </select>
        <select name="fstate" style="padding:.5rem .5rem;max-width:150px">
          <option value=""><?= htmlspecialchars($al['news_all_states'] ?? 'Všechny stavy') ?></option>
          <option value="active"   <?= $fState==='active'  ?'selected':'' ?>><?= htmlspecialchars($al['news_active']   ?? 'Aktivní') ?></option>
          <option value="inactive" <?= $fState==='inactive'?'selected':'' ?>><?= htmlspecialchars($al['news_inactive'] ?? 'Odhlášeno') ?></option>
        </select>
        <button type="submit" class="a-btn a-btn--primary" style="padding:.5rem 1rem">
          <?= icon('search') ?: '' ?> <?= htmlspecialchars($al['news_filter'] ?? 'Filtrovat') ?>
        </button>
        <?php if ($q !== '' || $fLang !== '' || $fState !== ''): ?>
          <a href="?page=newsletter" class="a-btn a-btn--outline" style="padding:.5rem .875rem"><?= htmlspecialchars($al['news_reset'] ?? 'Vymazat') ?></a>
        <?php endif; ?>
      </form>
    </div>

    <?php if (empty($rows)): ?>
      <div class="a-empty">
        <?= icon('users') ?>
        <p><?= htmlspecialchars($al['news_empty'] ?? 'Žádní odběratelé.') ?></p>
      </div>
    <?php else: ?>
      <div class="a-table-wrap">
        <table class="a-table">
          <thead>
            <tr>
              <th><?= htmlspecialchars($al['news_col_email'] ?? 'E-mail') ?></th>
              <th><?= htmlspecialchars($al['news_col_name'] ?? 'Jméno') ?></th>
              <th style="width:70px"><?= htmlspecialchars($al['news_col_lang'] ?? 'Jazyk') ?></th>
              <th style="width:100px"><?= htmlspecialchars($al['news_col_active'] ?? 'Stav') ?></th>
              <th style="width:110px"><?= htmlspecialchars($al['news_col_created'] ?? 'Přidán') ?></th>
              <th class="a-col-actions" style="width:60px"><?= htmlspecialchars($al['col_actions'] ?? '') ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr<?= $r['active'] ? '' : ' style="opacity:.55"' ?>>
                <td>
                  <a href="mailto:<?= htmlspecialchars($r['email']) ?>"
                     style="color:var(--a-ink-900,inherit);text-decoration:none;font-weight:600"
                     title="<?= htmlspecialchars($r['email']) ?>">
                    <?= htmlspecialchars($r['email']) ?>
                  </a>
                </td>
                <td style="color:var(--a-ink-500,var(--a-muted))"><?= htmlspecialchars($r['name'] ?: '—') ?></td>
                <td>
                  <span class="a-badge a-badge--cat"><?= htmlspecialchars(strtoupper($r['lang'])) ?></span>
                </td>
                <td>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="action"    value="toggle">
                    <input type="hidden" name="toggle_id" value="<?= $r['id'] ?>">
                    <button type="submit"
                            class="a-badge <?= $r['active'] ? 'a-badge--ok' : 'a-badge--off' ?>"
                            style="border:none;cursor:pointer;font-weight:600"
                            title="<?= htmlspecialchars($al['news_toggle_hint'] ?? 'Kliknutím přepnete stav') ?>">
                      <?= $r['active']
                            ? '✓ ' . htmlspecialchars($al['news_active']   ?? 'Aktivní')
                            : '✗ ' . htmlspecialchars($al['news_inactive'] ?? 'Odhlášeno') ?>
                    </button>
                  </form>
                </td>
                <td style="color:var(--a-muted);font-size:.85rem;white-space:nowrap">
                  <?= htmlspecialchars(date('d.m.Y', strtotime($r['created_at']))) ?>
                </td>
                <td class="a-col-actions">
                  <form method="post" style="display:inline"
                        onsubmit="return confirm(<?= json_encode($al['news_delete_confirm'] ?? 'Opravdu smazat tohoto odběratele?') ?>)">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="del_id" value="<?= $r['id'] ?>">
                    <button type="submit" class="a-btn a-btn--danger"
                            title="<?= htmlspecialchars($al['btn_delete'] ?? 'Smazat') ?>"
                            style="padding:.4rem .55rem"><?= icon('trash') ?></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <script>
  function showNewsForm() {
    const c = document.getElementById('news-form-card');
    c.style.display = '';
    c.scrollIntoView({ behavior: 'smooth', block: 'start' });
    const first = c.querySelector('input[name="email"]');
    if (first) first.focus();
  }
  function hideNewsForm() {
    document.getElementById('news-form-card').style.display = 'none';
  }
  </script>

<?php endif; ?>
