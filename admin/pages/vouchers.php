<?php
$msg   = '';
$error = '';
$edit  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $vid         = (int)($_POST['vid'] ?? 0);
        $code        = strtoupper(trim($_POST['code'] ?? ''));
        $type        = ($_POST['type'] ?? 'percent') === 'fixed' ? 'fixed' : 'percent';
        $amount      = max(0, (float)($_POST['amount'] ?? 0));
        $min_order   = max(0, (float)($_POST['min_order'] ?? 0));
        $valid_until = trim($_POST['valid_until'] ?? '') ?: null;
        $usage_limit = ($_POST['usage_limit'] ?? '') !== '' ? max(0, (int)$_POST['usage_limit']) : null;
        $active      = isset($_POST['active']) ? 1 : 0;

        if ($code === '' || $amount <= 0) {
            $error = $al['vou_err_required'] ?? 'Kód a hodnota jsou povinné.';
        } else {
            try {
                $dup = db()->prepare('SELECT id FROM vouchers WHERE code = ? AND id != ?');
                $dup->execute([$code, $vid]);
                if ($dup->fetch()) {
                    $error = $al['vou_err_code'] ?? 'Kód již existuje.';
                } else {
                    if ($vid) {
                        db()->prepare('UPDATE vouchers SET code=?, type=?, amount=?, min_order=?,
                                       valid_until=?, usage_limit=?, active=? WHERE id=?')
                            ->execute([$code, $type, $amount, $min_order, $valid_until, $usage_limit, $active, $vid]);
                        $msg = $al['vou_saved_msg'] ?? 'Voucher aktualizován.';
                    } else {
                        db()->prepare('INSERT INTO vouchers (code, type, amount, min_order, valid_until, usage_limit, active)
                                       VALUES (?,?,?,?,?,?,?)')
                            ->execute([$code, $type, $amount, $min_order, $valid_until, $usage_limit, $active]);
                        $msg = $al['vou_added_msg'] ?? 'Voucher přidán.';
                    }
                }
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
    }

    if ($action === 'delete' && !empty($_POST['vid'])) {
        db()->prepare('DELETE FROM vouchers WHERE id = ?')->execute([(int)$_POST['vid']]);
        $msg = $al['vou_deleted_msg'] ?? 'Voucher smazán.';
    }
}

$edit_id = (int)($_GET['edit'] ?? 0);
if ($edit_id) {
    $s = db()->prepare('SELECT * FROM vouchers WHERE id = ?');
    $s->execute([$edit_id]);
    $edit = $s->fetch() ?: null;
}
$is_new = isset($_GET['new']);

try {
    $vouchers = db()->query('SELECT * FROM vouchers ORDER BY active DESC, code')->fetchAll();
} catch (Exception $e) { $vouchers = []; }
?>

<?php if ($msg): ?>  <div class="a-alert a-alert--success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="a-alert a-alert--danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if ($edit || $is_new):
    $e = $edit ?: ['id'=>0,'code'=>'','type'=>'percent','amount'=>10,'min_order'=>0,'valid_until'=>'','usage_limit'=>'','active'=>1];
?>
<div class="a-card" style="max-width:680px;margin-bottom:1.5rem">
  <div class="a-card-head">
    <h2><?= htmlspecialchars($edit ? ($al['vou_edit_title'] ?? 'Upravit voucher') : ($al['vou_new_title'] ?? 'Nový voucher')) ?></h2>
  </div>
  <div class="a-card-body">
    <form method="post" class="a-form">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="vid"    value="<?= (int)$e['id'] ?>">
      <div class="a-form-row">
        <div class="a-field">
          <label><?= htmlspecialchars($al['vou_code'] ?? 'Kód *') ?></label>
          <input type="text" name="code" required style="text-transform:uppercase;font-family:var(--font-mono,monospace)"
                 value="<?= htmlspecialchars($e['code']) ?>" placeholder="SUMMER10">
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['vou_type'] ?? 'Typ') ?></label>
          <select name="type">
            <option value="percent" <?= $e['type']==='percent'?'selected':'' ?>>%</option>
            <option value="fixed"   <?= $e['type']==='fixed'  ?'selected':'' ?>>€ / Kč (fixed EUR)</option>
          </select>
        </div>
      </div>
      <div class="a-form-row">
        <div class="a-field">
          <label><?= htmlspecialchars($al['vou_amount'] ?? 'Hodnota *') ?></label>
          <input type="number" step="0.01" min="0" name="amount" required
                 value="<?= htmlspecialchars((string)$e['amount']) ?>">
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['vou_min_order'] ?? 'Min. objednávka (EUR)') ?></label>
          <input type="number" step="0.01" min="0" name="min_order"
                 value="<?= htmlspecialchars((string)$e['min_order']) ?>">
        </div>
      </div>
      <div class="a-form-row">
        <div class="a-field">
          <label><?= htmlspecialchars($al['vou_valid_until'] ?? 'Platný do') ?></label>
          <input type="date" name="valid_until" value="<?= htmlspecialchars((string)$e['valid_until']) ?>">
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['vou_usage_limit'] ?? 'Limit použití') ?></label>
          <input type="number" min="0" name="usage_limit" value="<?= htmlspecialchars((string)$e['usage_limit']) ?>">
        </div>
      </div>
      <label style="display:flex;align-items:center;gap:.5rem">
        <input type="checkbox" name="active" value="1" <?= $e['active']?'checked':'' ?>>
        <span><?= htmlspecialchars($al['vou_active'] ?? 'Aktivní') ?></span>
      </label>
      <div class="a-form-actions">
        <button type="submit" class="a-btn a-btn--primary"><?= icon('check') ?> <?= htmlspecialchars($al['vou_save'] ?? 'Uložit') ?></button>
        <a href="?page=vouchers" class="a-btn a-btn--outline"><?= htmlspecialchars($al['vou_cancel'] ?? 'Zrušit') ?></a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="a-card">
  <div class="a-card-head" style="display:flex;justify-content:space-between;align-items:center">
    <h2><?= htmlspecialchars($al['vou_all_title'] ?? 'Vouchery') ?></h2>
    <a href="?page=vouchers&new=1" class="a-btn a-btn--primary"><?= icon('plus') ?> <?= htmlspecialchars($al['vou_new'] ?? 'Nový voucher') ?></a>
  </div>
  <div class="a-card-body" style="padding:0">
    <?php if (!$vouchers): ?>
      <p style="padding:1rem;margin:0;color:var(--a-muted)"><?= htmlspecialchars($al['vou_empty'] ?? 'Žádné vouchery.') ?></p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr style="text-align:left;font-size:.8rem;color:var(--a-muted)">
            <th style="padding:.5rem 1rem">Kód</th>
            <th style="padding:.5rem 1rem">Sleva</th>
            <th style="padding:.5rem 1rem">Min.</th>
            <th style="padding:.5rem 1rem">Platný do</th>
            <th style="padding:.5rem 1rem">Použito</th>
            <th style="padding:.5rem 1rem">Aktivní</th>
            <th style="padding:.5rem 1rem"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($vouchers as $v): ?>
          <tr style="border-top:1px solid var(--a-border)">
            <td style="padding:.5rem 1rem;font-family:var(--font-mono,monospace);font-weight:600"><?= htmlspecialchars($v['code']) ?></td>
            <td style="padding:.5rem 1rem"><?= $v['type']==='percent' ? htmlspecialchars($v['amount']).' %' : '€ '.number_format((float)$v['amount'],2,',','.') ?></td>
            <td style="padding:.5rem 1rem"><?= (float)$v['min_order'] > 0 ? '€ '.number_format((float)$v['min_order'],2,',','.') : '—' ?></td>
            <td style="padding:.5rem 1rem"><?= $v['valid_until'] ? htmlspecialchars($v['valid_until']) : '—' ?></td>
            <td style="padding:.5rem 1rem"><?= (int)$v['used_count'] ?><?= $v['usage_limit']!==null ? ' / '.(int)$v['usage_limit'] : '' ?></td>
            <td style="padding:.5rem 1rem"><?= $v['active'] ? '✓' : '—' ?></td>
            <td style="padding:.5rem 1rem;text-align:right;white-space:nowrap">
              <a href="?page=vouchers&edit=<?= (int)$v['id'] ?>" class="a-btn a-btn--outline"><?= icon('edit') ?></a>
              <form method="post" style="display:inline" onsubmit="return confirm('<?= htmlspecialchars($al['vou_delete_confirm'] ?? 'Smazat voucher?') ?>')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="vid"    value="<?= (int)$v['id'] ?>">
                <button type="submit" class="a-btn a-btn--outline"><?= icon('trash') ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
