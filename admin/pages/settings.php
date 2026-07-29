<?php
define('SETTINGS_FILE', __DIR__ . '/../../data/settings.json');

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_contact') {
    $settings['site_studio_name'] = trim($_POST['site_studio_name'] ?? '');
    $settings['site_owner_name']  = trim($_POST['site_owner_name']  ?? '');
    $settings['site_street']      = trim($_POST['site_street']      ?? '');
    $settings['site_city_postal'] = trim($_POST['site_city_postal'] ?? '');
    $settings['site_phone']       = trim($_POST['site_phone']       ?? '');
    $settings['site_email']       = trim($_POST['site_email']       ?? '');
    $settings['site_ga_id']       = trim($_POST['site_ga_id']       ?? '');
    save_settings($settings);
    $msg = $al['set_saved_msg'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send_test_mail') {
    require_once __DIR__ . '/../../includes/mail.php';
    $to   = trim($_POST['test_to'] ?? '');
    $from = sprintf('%s <%s>',
        $settings['site_studio_name'] ?? 'Expert OPTIC',
        $settings['site_email']       ?? 'brno@tstoptik.com'
    );
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $msg = $al['set_mail_invalid'] ?? 'Neplatná e-mailová adresa.';
    } else {
        $via  = (defined('SMTP_HOST') && SMTP_HOST !== '')
                ? 'SMTP (' . SMTP_HOST . ':' . SMTP_PORT . ')'
                : 'PHP mail()';
        $body = "expert·optic — test e-mail\n\n"
              . "Odesláno přes: {$via}\n"
              . 'Čas: ' . date('Y-m-d H:i:s') . "\n";
        $ok = _mail_send($to, 'Test — expert·optic admin', $body, $from);
        $msg = $ok
            ? sprintf($al['set_mail_sent'] ?? 'Test e-mail odeslán na %s.', $to)
            : ($al['set_mail_failed'] ?? 'Odeslání selhalo. Zkontrolujte SMTP konfiguraci.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_shipping') {
    $settings['ship_personal_enabled']   = isset($_POST['ship_personal_enabled']) ? 1 : 0;
    $settings['ship_personal_address']   = trim($_POST['ship_personal_address'] ?? '');
    $settings['ship_personal_hours']     = trim($_POST['ship_personal_hours'] ?? '');
    $settings['ship_balikovna_enabled']  = isset($_POST['ship_balikovna_enabled']) ? 1 : 0;
    $settings['ship_balikovna_cost']     = max(0, (float)($_POST['ship_balikovna_cost'] ?? 0));
    $settings['ship_balikovna_home_enabled'] = isset($_POST['ship_balikovna_home_enabled']) ? 1 : 0;
    $settings['ship_balikovna_home_cost']    = max(0, (float)($_POST['ship_balikovna_home_cost'] ?? 0));
    $settings['ship_free_threshold']     = max(0, (float)($_POST['ship_free_threshold'] ?? 0));
    save_settings($settings);
    $msg = $al['set_saved_msg'];
}
?>

<?php if ($msg): ?><div class="a-alert a-alert--success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<!-- Studio contact & Google Analytics -->
<div class="a-card" style="max-width:680px;margin-bottom:1.5rem">
  <div class="a-card-head">
    <h2><?= htmlspecialchars($al['set_contact_title']) ?></h2>
  </div>
  <div class="a-card-body">
    <form method="post" class="a-form">
      <input type="hidden" name="action" value="save_contact">

      <div class="a-form-row">
        <div class="a-field">
          <label><?= htmlspecialchars($al['set_studio_name']) ?></label>
          <input type="text" name="site_studio_name"
                 value="<?= htmlspecialchars($settings['site_studio_name'] ?? 'Optické a optometristické studio') ?>"
                 placeholder="Optické a optometristické studio">
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['set_owner_name']) ?></label>
          <input type="text" name="site_owner_name"
                 value="<?= htmlspecialchars($settings['site_owner_name'] ?? 'Thomas Scheibl') ?>"
                 placeholder="Thomas Scheibl">
        </div>
      </div>

      <div class="a-form-row">
        <div class="a-field">
          <label><?= htmlspecialchars($al['set_street']) ?></label>
          <input type="text" name="site_street"
                 value="<?= htmlspecialchars($settings['site_street'] ?? 'Hlavní 131') ?>"
                 placeholder="Hlavní 131">
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['set_city_postal']) ?></label>
          <input type="text" name="site_city_postal"
                 value="<?= htmlspecialchars($settings['site_city_postal'] ?? '624 00 Brno-Komín') ?>"
                 placeholder="624 00 Brno-Komín">
        </div>
      </div>

      <div class="a-form-row">
        <div class="a-field">
          <label><?= htmlspecialchars($al['set_phone']) ?></label>
          <input type="tel" name="site_phone"
                 value="<?= htmlspecialchars($settings['site_phone'] ?? '+420 603 419 882') ?>"
                 placeholder="+420 603 419 882">
        </div>
        <div class="a-field">
          <label><?= htmlspecialchars($al['set_email']) ?></label>
          <input type="email" name="site_email"
                 value="<?= htmlspecialchars($settings['site_email'] ?? 'brno@tstoptik.com') ?>"
                 placeholder="brno@tstoptik.com">
        </div>
      </div>

      <div class="a-field">
        <label><?= htmlspecialchars($al['set_ga_id']) ?></label>
        <input type="text" name="site_ga_id"
               value="<?= htmlspecialchars($settings['site_ga_id'] ?? '') ?>"
               placeholder="G-XXXXXXXXXX"
               style="font-family:var(--font-mono,monospace);max-width:280px">
        <small style="color:var(--a-muted);display:block;margin-top:.375rem">
          <?= htmlspecialchars($al['set_ga_hint']) ?>
        </small>
      </div>

      <div class="a-form-actions">
        <button type="submit" class="a-btn a-btn--primary">
          <?= icon('check') ?> <?= htmlspecialchars($al['set_save']) ?>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Shipping methods -->
<div class="a-card" style="max-width:680px;margin-bottom:1.5rem">
  <div class="a-card-head">
    <h2><?= htmlspecialchars($al['set_ship_title']) ?></h2>
  </div>
  <div class="a-card-body">
    <form method="post" class="a-form">
      <input type="hidden" name="action" value="save_shipping">

      <!-- Personal pickup -->
      <fieldset style="border:1px solid var(--a-border);border-radius:8px;padding:1rem 1.25rem;margin-bottom:1rem">
        <legend style="padding:0 .5rem;font-weight:600;font-size:.875rem">
          <?= htmlspecialchars($al['set_ship_personal']) ?>
        </legend>

        <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;cursor:pointer">
          <input type="checkbox" name="ship_personal_enabled" value="1"
                 <?= !empty($settings['ship_personal_enabled']) || !isset($settings['ship_personal_enabled']) ? 'checked' : '' ?>>
          <span><?= htmlspecialchars($al['set_ship_enabled']) ?></span>
          <span style="color:var(--a-muted);font-size:.8rem;margin-left:auto">
            <?= htmlspecialchars($al['set_ship_free']) ?>
          </span>
        </label>

        <div class="a-field">
          <label><?= htmlspecialchars($al['set_ship_personal_addr']) ?></label>
          <input type="text" name="ship_personal_address"
                 value="<?= htmlspecialchars($settings['ship_personal_address'] ?? 'Expert OPTIC · Brno-Komín, Hlavní 131') ?>"
                 placeholder="Expert OPTIC · Brno-Komín, Hlavní 131">
        </div>

        <div class="a-field">
          <label><?= htmlspecialchars($al['set_ship_personal_hours']) ?></label>
          <input type="text" name="ship_personal_hours"
                 value="<?= htmlspecialchars($settings['ship_personal_hours'] ?? '') ?>"
                 placeholder="<?= htmlspecialchars($al['set_ship_hours_ph']) ?>">
        </div>
      </fieldset>

      <!-- Balíkovna pickup -->
      <fieldset style="border:1px solid var(--a-border);border-radius:8px;padding:1rem 1.25rem;margin-bottom:1rem">
        <legend style="padding:0 .5rem;font-weight:600;font-size:.875rem">
          <?= htmlspecialchars($al['set_ship_bali']) ?>
        </legend>

        <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;cursor:pointer">
          <input type="checkbox" name="ship_balikovna_enabled" value="1"
                 <?= !empty($settings['ship_balikovna_enabled']) || !isset($settings['ship_balikovna_enabled']) ? 'checked' : '' ?>>
          <span><?= htmlspecialchars($al['set_ship_enabled']) ?></span>
        </label>

        <div class="a-field">
          <label><?= htmlspecialchars($al['set_ship_cost']) ?> (Kč)</label>
          <input type="number" name="ship_balikovna_cost" min="0" step="1"
                 value="<?= htmlspecialchars((string)($settings['ship_balikovna_cost'] ?? 89)) ?>"
                 placeholder="89" style="max-width:180px">
        </div>

        <p style="font-size:.8rem;color:var(--a-muted);margin:.5rem 0 0">
          <?= htmlspecialchars($al['set_ship_bali_hint']) ?>
        </p>
      </fieldset>

      <!-- Balíkovna home delivery -->
      <fieldset style="border:1px solid var(--a-border);border-radius:8px;padding:1rem 1.25rem;margin-bottom:1rem">
        <legend style="padding:0 .5rem;font-weight:600;font-size:.875rem">
          <?= htmlspecialchars($al['set_ship_bali_home']) ?>
        </legend>

        <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;cursor:pointer">
          <input type="checkbox" name="ship_balikovna_home_enabled" value="1"
                 <?= !empty($settings['ship_balikovna_home_enabled']) || !isset($settings['ship_balikovna_home_enabled']) ? 'checked' : '' ?>>
          <span><?= htmlspecialchars($al['set_ship_enabled']) ?></span>
        </label>

        <div class="a-field">
          <label><?= htmlspecialchars($al['set_ship_cost']) ?> (Kč)</label>
          <input type="number" name="ship_balikovna_home_cost" min="0" step="1"
                 value="<?= htmlspecialchars((string)($settings['ship_balikovna_home_cost'] ?? 119)) ?>"
                 placeholder="119" style="max-width:180px">
        </div>
      </fieldset>

      <!-- Free shipping threshold -->
      <div class="a-field">
        <label><?= htmlspecialchars($al['set_ship_free_threshold']) ?> (Kč)</label>
        <input type="number" name="ship_free_threshold" min="0" step="1"
               value="<?= htmlspecialchars((string)($settings['ship_free_threshold'] ?? 0)) ?>"
               placeholder="0" style="max-width:180px">
        <small style="color:var(--a-muted);display:block;margin-top:.375rem">
          <?= htmlspecialchars($al['set_ship_free_threshold_hint']) ?>
        </small>
      </div>

      <div class="a-form-actions">
        <button type="submit" class="a-btn a-btn--primary">
          <?= icon('check') ?> <?= htmlspecialchars($al['set_save']) ?>
        </button>
      </div>
    </form>
  </div>
</div>

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

<!-- Test mail -->
<div class="a-card" style="max-width:680px;margin-top:1.5rem">
  <div class="a-card-head">
    <h2><?= htmlspecialchars($al['set_mail_title'] ?? 'Testovací e-mail') ?></h2>
  </div>
  <div class="a-card-body">
    <p style="font-size:.875rem;color:var(--a-muted);margin:0 0 1rem">
      <?= htmlspecialchars($al['set_mail_mode'] ?? 'Režim') ?>:
      <strong>
        <?= defined('SMTP_HOST') && SMTP_HOST !== ''
              ? 'SMTP · ' . htmlspecialchars(SMTP_HOST . ':' . SMTP_PORT . (SMTP_SECURE ? ' (' . SMTP_SECURE . ')' : ''))
              : 'PHP mail()' ?>
      </strong>
    </p>
    <form method="post" class="a-form">
      <input type="hidden" name="action" value="send_test_mail">
      <div class="a-field">
        <label><?= htmlspecialchars($al['set_mail_to'] ?? 'Příjemce') ?></label>
        <input type="email" name="test_to" required
               value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>"
               placeholder="test@example.com" style="max-width:320px">
      </div>
      <div class="a-form-actions">
        <button type="submit" class="a-btn a-btn--primary">
          <?= icon('check') ?> <?= htmlspecialchars($al['set_mail_send'] ?? 'Odeslat test') ?>
        </button>
      </div>
    </form>
  </div>
</div>
