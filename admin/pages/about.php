<?php
/* System / configuration overview. Read-only. */

function _about_mask(string $v, int $keep = 2): string {
    if ($v === '') return '—';
    $len = mb_strlen($v);
    if ($len <= $keep) return str_repeat('•', $len);
    return mb_substr($v, 0, $keep) . str_repeat('•', min(8, $len - $keep));
}
function _about_bool(bool $b): string {
    return $b ? '<span style="color:#16a34a">✓</span>' : '<span style="color:#dc2626">✗</span>';
}
function _about_writable(string $path): string {
    if (!file_exists($path)) return '<span style="color:#a16207">— (missing)</span>';
    return is_writable($path)
        ? '<span style="color:#16a34a">writable</span>'
        : '<span style="color:#dc2626">read-only</span>';
}

/* Server / PHP */
try { $db_server_version = db()->getAttribute(PDO::ATTR_SERVER_VERSION); }
catch (Exception $e) { $db_server_version = '—'; }
try { $db_client_version = db()->getAttribute(PDO::ATTR_CLIENT_VERSION); }
catch (Exception $e) { $db_client_version = '—'; }

/* Runtime settings (data/settings.json) */
$s_file = __DIR__ . '/../../data/settings.json';
$s_data = [];
if (file_exists($s_file)) {
    $d = json_decode(file_get_contents($s_file), true);
    if (is_array($d)) $s_data = $d;
}

/* SMTP status */
$smtp_active = defined('SMTP_HOST') && SMTP_HOST !== '';

/* Admin users */
try {
    $admin_users = db()->query('SELECT id, username, email, role, active, last_login
                                FROM admin_users ORDER BY username')->fetchAll();
} catch (Exception $e) { $admin_users = []; }

/* Row helper for tables */
function _about_row(string $k, string $v_html): void {
    echo '<tr><th style="text-align:left;padding:.4rem .75rem;color:var(--a-muted);font-weight:500;white-space:nowrap">'
       . htmlspecialchars($k)
       . '</th><td style="padding:.4rem .75rem;font-family:var(--font-mono,monospace);word-break:break-all">'
       . $v_html . '</td></tr>';
}
?>

<style>
  .a-about-card table { width:100%; border-collapse:collapse }
  .a-about-card tr + tr th, .a-about-card tr + tr td { border-top:1px solid var(--a-border) }
</style>

<div class="a-card a-about-card" style="max-width:840px;margin-bottom:1.5rem">
  <div class="a-card-head"><h2><?= htmlspecialchars($al['about_web_title'] ?? 'Web / server') ?></h2></div>
  <div class="a-card-body" style="padding:0">
    <table>
      <?php
      _about_row('PHP',                htmlspecialchars(PHP_VERSION) . ' (' . htmlspecialchars(PHP_SAPI) . ')');
      _about_row('Server',             htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? '—'));
      _about_row('Host',               htmlspecialchars($_SERVER['HTTP_HOST'] ?? '—'));
      _about_row('Document root',      htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? '—'));
      _about_row('Timezone',           htmlspecialchars(date_default_timezone_get()) . ' · ' . date('Y-m-d H:i:s'));
      _about_row('Session',            htmlspecialchars(session_name()) . ' = ' . _about_mask(session_id(), 4));
      ?>
    </table>
  </div>
</div>

<div class="a-card a-about-card" style="max-width:840px;margin-bottom:1.5rem">
  <div class="a-card-head"><h2><?= htmlspecialchars($al['about_db_title'] ?? 'Databáze') ?></h2></div>
  <div class="a-card-body" style="padding:0">
    <table>
      <?php
      _about_row('Host',     htmlspecialchars(DB_HOST));
      _about_row('Database', htmlspecialchars(DB_NAME));
      _about_row('User',     htmlspecialchars(DB_USER));
      _about_row('Password', _about_mask(DB_PASS));
      _about_row('Charset',  htmlspecialchars(DB_CHARSET));
      _about_row('Server version', htmlspecialchars((string)$db_server_version));
      _about_row('Client version', htmlspecialchars((string)$db_client_version));
      ?>
    </table>
  </div>
</div>

<div class="a-card a-about-card" style="max-width:840px;margin-bottom:1.5rem">
  <div class="a-card-head"><h2><?= htmlspecialchars($al['about_mail_title'] ?? 'Odchozí pošta (SMTP)') ?></h2></div>
  <div class="a-card-body" style="padding:0">
    <table>
      <?php
      _about_row('Režim / Mode',
          $smtp_active
              ? '<span style="color:#16a34a">SMTP</span>'
              : '<span style="color:#a16207">PHP mail() — SMTP not configured</span>');
      _about_row('Host',   htmlspecialchars(defined('SMTP_HOST') ? SMTP_HOST : '') ?: '—');
      _about_row('Port',   defined('SMTP_PORT') ? (string)SMTP_PORT : '—');
      _about_row('User',   htmlspecialchars(defined('SMTP_USER') ? SMTP_USER : '') ?: '—');
      _about_row('Password', defined('SMTP_PASS') ? _about_mask(SMTP_PASS) : '—');
      _about_row('Secure', htmlspecialchars(defined('SMTP_SECURE') ? SMTP_SECURE : '') ?: '—');
      _about_row('From',   htmlspecialchars(defined('SMTP_FROM')   ? SMTP_FROM   : '') ?: '—');
      ?>
    </table>
  </div>
</div>

<div class="a-card a-about-card" style="max-width:840px;margin-bottom:1.5rem">
  <div class="a-card-head"><h2><?= htmlspecialchars($al['about_fs_title'] ?? 'Souborový systém') ?></h2></div>
  <div class="a-card-body" style="padding:0">
    <table>
      <?php
      $data_dir      = realpath(__DIR__ . '/../../data') ?: __DIR__ . '/../../data';
      $settings_file = $data_dir . DIRECTORY_SEPARATOR . 'settings.json';
      $bookings_file = $data_dir . DIRECTORY_SEPARATOR . 'bookings.json';
      $assets_dir    = realpath(__DIR__ . '/../../assets') ?: __DIR__ . '/../../assets';
      _about_row('data/',           htmlspecialchars($data_dir)      . ' — ' . _about_writable($data_dir));
      _about_row('settings.json',   htmlspecialchars($settings_file) . ' — ' . _about_writable($settings_file));
      _about_row('bookings.json',   htmlspecialchars($bookings_file) . ' — ' . _about_writable($bookings_file));
      _about_row('assets/',         htmlspecialchars($assets_dir)    . ' — ' . _about_writable($assets_dir));
      ?>
    </table>
  </div>
</div>

<div class="a-card a-about-card" style="max-width:840px;margin-bottom:1.5rem">
  <div class="a-card-head"><h2><?= htmlspecialchars($al['about_app_title'] ?? 'Aplikace') ?></h2></div>
  <div class="a-card-body" style="padding:0">
    <table>
      <?php
      _about_row('Studio',       htmlspecialchars($s_data['site_studio_name'] ?? '—'));
      _about_row('E-mail',       htmlspecialchars($s_data['site_email']       ?? '—'));
      _about_row('Telefon',      htmlspecialchars($s_data['site_phone']       ?? '—'));
      _about_row('GA ID',        htmlspecialchars($s_data['site_ga_id']       ?? '—') ?: '—');
      _about_row('EUR → CZK',    (string)EUR_TO_CZK);
      _about_row('IBAN',         _about_mask((string)($s_data['bank_iban'] ?? ''), 4));
      ?>
    </table>
  </div>
</div>

<div class="a-card a-about-card" style="max-width:840px;margin-bottom:1.5rem">
  <div class="a-card-head"><h2><?= htmlspecialchars($al['about_users_title'] ?? 'Administrátoři') ?></h2></div>
  <div class="a-card-body" style="padding:0">
    <?php if (!$admin_users): ?>
      <p style="padding:.75rem 1rem;margin:0;color:var(--a-muted)">—</p>
    <?php else: ?>
      <table>
        <?php foreach ($admin_users as $u): ?>
          <tr>
            <th style="text-align:left;padding:.4rem .75rem;font-weight:500;white-space:nowrap">
              <a href="index.php?page=users&amp;edit=<?= (int)$u['id'] ?>">
                <?= htmlspecialchars($u['username']) ?>
              </a>
              <?php if (!$u['active']): ?>
                <span style="color:#dc2626;font-size:.75rem;margin-left:.5rem">(inactive)</span>
              <?php endif; ?>
            </th>
            <td style="padding:.4rem .75rem;font-family:var(--font-mono,monospace);word-break:break-all">
              <?= htmlspecialchars($u['email']) ?>
              · <?= htmlspecialchars($u['role']) ?>
              <?php if ($u['last_login']): ?>
                · <span style="color:var(--a-muted)"><?= htmlspecialchars($u['last_login']) ?></span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>

<div class="a-card a-about-card" style="max-width:840px">
  <div class="a-card-head"><h2><?= htmlspecialchars($al['about_ext_title'] ?? 'PHP rozšíření') ?></h2></div>
  <div class="a-card-body" style="padding:0">
    <table>
      <?php
      foreach (['pdo_mysql','mbstring','openssl','json','curl','gd','fileinfo'] as $ext) {
          _about_row($ext, _about_bool(extension_loaded($ext)));
      }
      ?>
    </table>
  </div>
</div>
