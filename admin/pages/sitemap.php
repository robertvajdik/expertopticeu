<?php
/* Sitemap generator — writes /sitemap.xml at the site root */

if (!defined('SETTINGS_FILE')) define('SETTINGS_FILE', __DIR__ . '/../../data/settings.json');
if (!function_exists('load_settings')) {
    function load_settings(): array {
        if (!file_exists(SETTINGS_FILE)) return [];
        $d = json_decode(file_get_contents(SETTINGS_FILE), true);
        return is_array($d) ? $d : [];
    }
}
if (!function_exists('save_settings')) {
    function save_settings(array $data): void {
        file_put_contents(SETTINGS_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

define('SITEMAP_FILE', __DIR__ . '/../../sitemap.xml');

$settings   = load_settings();
$base_url   = rtrim($settings['site_base_url'] ?? 'https://exportoptic.eu', '/');
$site_langs = ['at', 'en', 'cz'];
$hreflang   = ['at' => 'de', 'en' => 'en', 'cz' => 'cs'];

/* Static routes with default change frequency and priority */
$static_routes = [
    'home'           => ['freq' => 'weekly',  'priority' => '1.0'],
    'collection'     => ['freq' => 'weekly',  'priority' => '0.9'],
    'sport-glasses'  => ['freq' => 'monthly', 'priority' => '0.8'],
    'contact-lenses' => ['freq' => 'monthly', 'priority' => '0.8'],
    'brno'           => ['freq' => 'monthly', 'priority' => '0.7'],
    'booking'        => ['freq' => 'monthly', 'priority' => '0.7'],
];

/* Load products for dynamic URLs */
try {
    $sitemap_products = db()->query('SELECT id FROM products ORDER BY id')->fetchAll();
} catch (Exception $e) {
    $sitemap_products = [];
}

$msg = '';
$msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_base_url') {
        $url = trim($_POST['site_base_url'] ?? '');
        if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
            $settings['site_base_url'] = rtrim($url, '/');
            save_settings($settings);
            $base_url = $settings['site_base_url'];
            $msg = $al['sm_url_saved'];
        } else {
            $msg = $al['sm_url_invalid'];
            $msg_type = 'error';
        }
    }

    if ($action === 'generate') {
        $xml_lines = [];
        $xml_lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml_lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
                    . ' xmlns:xhtml="http://www.w3.org/1999/xhtml">';

        $today = date('Y-m-d');

        $emit_url = function (string $path_qs, string $freq, string $priority)
                    use (&$xml_lines, $base_url, $site_langs, $hreflang, $today) {
            /* Emit one <url> block per language, each with hreflang alternates */
            foreach ($site_langs as $lng) {
                $sep = str_contains($path_qs, '?') ? '&' : '?';
                $loc = $base_url . '/' . $path_qs . $sep . 'lang=' . $lng;
                $xml_lines[] = '  <url>';
                $xml_lines[] = '    <loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>';
                $xml_lines[] = '    <lastmod>' . $today . '</lastmod>';
                $xml_lines[] = '    <changefreq>' . $freq . '</changefreq>';
                $xml_lines[] = '    <priority>' . $priority . '</priority>';
                foreach ($site_langs as $alt) {
                    $alt_loc = $base_url . '/' . $path_qs . $sep . 'lang=' . $alt;
                    $xml_lines[] = '    <xhtml:link rel="alternate" hreflang="'
                                 . $hreflang[$alt] . '" href="'
                                 . htmlspecialchars($alt_loc, ENT_XML1) . '"/>';
                }
                $xml_lines[] = '  </url>';
            }
        };

        foreach ($static_routes as $slug => $meta) {
            $path_qs = ($slug === 'home') ? '' : '?p=' . $slug;
            $emit_url($path_qs, $meta['freq'], $meta['priority']);
        }

        foreach ($sitemap_products as $p) {
            $emit_url('?p=product&id=' . urlencode($p['id']), 'monthly', '0.6');
        }

        $xml_lines[] = '</urlset>';
        $xml = implode("\n", $xml_lines) . "\n";

        if (@file_put_contents(SITEMAP_FILE, $xml) !== false) {
            $count_urls = count($static_routes) * count($site_langs)
                        + count($sitemap_products) * count($site_langs);
            $msg = sprintf($al['sm_generated'], $count_urls);
        } else {
            $msg = $al['sm_write_error'];
            $msg_type = 'error';
        }
    }
}

$sitemap_exists = file_exists(SITEMAP_FILE);
$sitemap_mtime  = $sitemap_exists ? date('Y-m-d H:i', filemtime(SITEMAP_FILE)) : null;
$sitemap_size   = $sitemap_exists ? filesize(SITEMAP_FILE) : 0;
$sitemap_url    = $base_url . '/sitemap.xml';

$total_urls = (count($static_routes) + count($sitemap_products)) * count($site_langs);
?>

<?php if ($msg): ?>
  <div class="a-alert a-alert--<?= $msg_type === 'error' ? 'error' : 'success' ?>">
    <?= htmlspecialchars($msg) ?>
  </div>
<?php endif; ?>

<div class="a-dash-grid">

  <!-- Status card -->
  <div class="a-card">
    <div class="a-card-head">
      <h2><?= htmlspecialchars($al['sm_status_title']) ?></h2>
    </div>
    <div class="a-card-body">
      <?php if ($sitemap_exists): ?>
        <div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:1rem">
          <div style="display:flex;justify-content:space-between;gap:1rem">
            <span style="color:var(--a-muted)"><?= htmlspecialchars($al['sm_last_generated']) ?></span>
            <strong><?= htmlspecialchars($sitemap_mtime) ?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;gap:1rem">
            <span style="color:var(--a-muted)"><?= htmlspecialchars($al['sm_file_size']) ?></span>
            <strong><?= number_format($sitemap_size / 1024, 1) ?> KB</strong>
          </div>
          <div style="display:flex;justify-content:space-between;gap:1rem">
            <span style="color:var(--a-muted)"><?= htmlspecialchars($al['sm_public_url']) ?></span>
            <a href="<?= htmlspecialchars($sitemap_url) ?>" target="_blank"
               style="font-family:var(--font-mono,monospace);font-size:.8125rem">
              <?= htmlspecialchars($sitemap_url) ?>
            </a>
          </div>
        </div>
      <?php else: ?>
        <p style="color:var(--a-muted);margin:0 0 1rem">
          <?= htmlspecialchars($al['sm_no_file']) ?>
        </p>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="action" value="generate">
        <button type="submit" class="a-btn a-btn--primary">
          <?= icon('check') ?>
          <?= htmlspecialchars($sitemap_exists ? $al['sm_regenerate'] : $al['sm_generate']) ?>
        </button>
      </form>
    </div>
  </div>

  <!-- Summary card -->
  <div class="a-card">
    <div class="a-card-head">
      <h2><?= htmlspecialchars($al['sm_summary_title']) ?></h2>
    </div>
    <div class="a-card-body">
      <div style="display:flex;flex-direction:column;gap:.5rem">
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--a-muted)"><?= htmlspecialchars($al['sm_static_pages']) ?></span>
          <strong><?= count($static_routes) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--a-muted)"><?= htmlspecialchars($al['sm_products']) ?></span>
          <strong><?= count($sitemap_products) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span style="color:var(--a-muted)"><?= htmlspecialchars($al['sm_langs']) ?></span>
          <strong><?= count($site_langs) ?> (<?= implode(', ', array_map('strtoupper', $site_langs)) ?>)</strong>
        </div>
        <div style="display:flex;justify-content:space-between;border-top:1px solid var(--a-border);padding-top:.5rem;margin-top:.25rem">
          <span><strong><?= htmlspecialchars($al['sm_total_urls']) ?></strong></span>
          <strong><?= $total_urls ?></strong>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Base URL settings -->
<div class="a-card" style="margin-top:1.5rem;max-width:680px">
  <div class="a-card-head">
    <h2><?= htmlspecialchars($al['sm_settings_title']) ?></h2>
  </div>
  <div class="a-card-body">
    <form method="post" class="a-form">
      <input type="hidden" name="action" value="save_base_url">
      <div class="a-field">
        <label><?= htmlspecialchars($al['sm_base_url']) ?></label>
        <input type="url" name="site_base_url"
               value="<?= htmlspecialchars($base_url) ?>"
               placeholder="https://exportoptic.eu"
               style="font-family:var(--font-mono,monospace)">
        <small style="color:var(--a-muted);display:block;margin-top:.375rem">
          <?= htmlspecialchars($al['sm_base_url_hint']) ?>
        </small>
      </div>
      <div class="a-form-actions">
        <button type="submit" class="a-btn a-btn--outline">
          <?= icon('check') ?> <?= htmlspecialchars($al['sm_save_url']) ?>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Included pages list -->
<div class="a-card" style="margin-top:1.5rem">
  <div class="a-card-head">
    <h2><?= htmlspecialchars($al['sm_included_title']) ?></h2>
  </div>
  <div class="a-table-wrap">
    <table class="a-table">
      <thead>
        <tr>
          <th><?= htmlspecialchars($al['sm_col_path']) ?></th>
          <th><?= htmlspecialchars($al['sm_col_freq']) ?></th>
          <th><?= htmlspecialchars($al['sm_col_priority']) ?></th>
          <th><?= htmlspecialchars($al['sm_col_langs']) ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($static_routes as $slug => $meta): ?>
          <tr>
            <td>
              <code style="font-size:.8125rem">
                <?= $slug === 'home' ? '/' : '/?p=' . htmlspecialchars($slug) ?>
              </code>
            </td>
            <td><?= htmlspecialchars($meta['freq']) ?></td>
            <td><?= htmlspecialchars($meta['priority']) ?></td>
            <td><?= count($site_langs) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!empty($sitemap_products)): ?>
          <tr>
            <td>
              <code style="font-size:.8125rem">/?p=product&amp;id=…</code>
              <small style="color:var(--a-muted);margin-left:.5rem">
                × <?= count($sitemap_products) ?>
              </small>
            </td>
            <td>monthly</td>
            <td>0.6</td>
            <td><?= count($site_langs) ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
