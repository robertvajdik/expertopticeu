<?php
define('SETTINGS_FILE', DATA_DIR . 'settings.json');

function load_settings(): array {
    if (!file_exists(SETTINGS_FILE)) return [];
    $d = json_decode(file_get_contents(SETTINGS_FILE), true);
    return is_array($d) ? $d : [];
}
function save_settings(array $data): void {
    file_put_contents(SETTINGS_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$settings = load_settings();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_settings') {
    $settings['bank_name'] = trim($_POST['bank_name'] ?? '');
    $settings['bank_iban'] = strtoupper(preg_replace('/\s+/', '', trim($_POST['bank_iban'] ?? '')));
    $settings['bank_bic']  = strtoupper(trim($_POST['bank_bic']  ?? ''));
    $settings['bank_ref']  = trim($_POST['bank_ref']  ?? '');
    $settings['bank_note'] = trim($_POST['bank_note'] ?? '');
    save_settings($settings);
    $msg = $al['set_saved_msg'];
}
?>

<?php if ($msg): ?><div class="a-alert a-alert--success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="a-card" style="max-width:680px">
  <div class="a-card-head">
    <h2><?= htmlspecialchars($al['set_bank_title']) ?></h2>
  </div>
  <div class="a-card-body">
    <form method="post" class="a-form">
      <input type="hidden" name="action" value="save_settings">

      <div class="a-form-row">
        <div class="a-field">
          <label><?= htmlspecialchars($al['set_bank_name']) ?></label>
          <input type="text" name="bank_name"
                 value="<?= htmlspecialchars($settings['bank_name'] ?? '') ?>"
                 placeholder="expert·optic s.r.o.">
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['set_bank_bic']) ?></label>
          <input type="text" name="bank_bic"
                 value="<?= htmlspecialchars($settings['bank_bic'] ?? '') ?>"
                 placeholder="KOMBCZPP" style="font-family:var(--font-mono,monospace)">
        </div>
      </div>

      <div class="a-field">
        <label><?= htmlspecialchars($al['set_bank_iban']) ?></label>
        <input type="text" name="bank_iban"
               value="<?= htmlspecialchars($settings['bank_iban'] ?? '') ?>"
               placeholder="CZ65 0800 0000 1920 0014 5399"
               style="font-family:var(--font-mono,monospace)">
      </div>

      <div class="a-form-row">
        <div class="a-field">
          <label><?= htmlspecialchars($al['set_bank_ref']) ?></label>
          <input type="text" name="bank_ref"
                 value="<?= htmlspecialchars($settings['bank_ref'] ?? 'Objednavka {nr}') ?>"
                 placeholder="<?= htmlspecialchars($al['set_bank_ref_ph']) ?>">
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['set_bank_note']) ?></label>
          <input type="text" name="bank_note"
                 value="<?= htmlspecialchars($settings['bank_note'] ?? 'expert·optic') ?>"
                 placeholder="<?= htmlspecialchars($al['set_bank_note_ph']) ?>">
        </div>
      </div>

      <div class="a-form-actions">
        <button type="submit" class="a-btn a-btn--primary">
          <?= icon('check') ?> <?= htmlspecialchars($al['set_save']) ?>
        </button>
      </div>
    </form>
  </div>
</div>
