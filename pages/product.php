<?php
$p       = $product;
$added   = isset($_GET['added']);
$tab     = $_GET['tab'] ?? 'desc';
$colors  = ['#2f5247','#a45e2a','#1b1714'];
$panels  = [
  'desc'  => $t['panel_desc'],
  'specs' => $t['panel_specs'],
  'care'  => $t['panel_care'],
];
$tabs = [
  'desc'  => $t['tab_desc'],
  'specs' => $t['tab_specs'],
  'care'  => $t['tab_care'],
];

/* ── Studio booking handler ── */
$studio_booked = false;
$studio_errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['studio_book'])) {
    $s_name  = trim($_POST['s_name']  ?? '');
    $s_email = trim($_POST['s_email'] ?? '');
    $s_phone = trim($_POST['s_phone'] ?? '');
    $s_date  = trim($_POST['s_date']  ?? '');

    if (!$s_name)                                   $studio_errors[] = $t['err_firstname'];
    if (!filter_var($s_email, FILTER_VALIDATE_EMAIL)) $studio_errors[] = $t['err_email'];
    if (!$s_date)                                   $studio_errors[] = $t['err_date'];

    if (empty($studio_errors)) {
        $bookings_file = __DIR__ . '/../data/bookings.json';
        $all = file_exists($bookings_file)
            ? (json_decode(file_get_contents($bookings_file), true) ?: [])
            : [];
        $all[] = [
            'id'        => uniqid('b', true),
            'timestamp' => date('Y-m-d H:i:s'),
            'vorname'   => $s_name,
            'nachname'  => '',
            'email'     => $s_email,
            'phone'     => $s_phone,
            'concern'   => 'Studio: ' . $p['brand'] . ' ' . $p['name'],
            'termin'    => $s_date,
            'sms'       => false,
            'lang'      => $lang,
            'status'    => 'new',
        ];
        file_put_contents($bookings_file, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $studio_booked = true;
    }
}
?>

<div class="page-wrap">
  <a href="?p=collection&amp;lang=<?= $lang ?>" class="back-link">
    <i data-lucide="arrow-left" style="width:16px;height:16px"></i>
    <?= htmlspecialchars($t['prod_back']) ?>
  </a>

  <div class="product-detail">
    <!-- Gallery -->
    <div class="product-gallery">
      <?php if (!empty($p['img'])): ?>
        <div class="product-photo-card">
          <img src="<?= htmlspecialchars($p['img']) ?>"
               alt="<?= htmlspecialchars($p['brand'] . ' ' . $p['name']) ?>"
               class="product-photo">
        </div>
        <?php if (!empty($p['img_ai'])): ?>
        <div class="product-thumbs">
          <button class="frame-tile product-img-thumb product-img-thumb--active"
                  data-img="<?= htmlspecialchars($p['img']) ?>"
                  aria-label="Produktfoto">
            <img src="<?= htmlspecialchars($p['img']) ?>"
                 alt="<?= htmlspecialchars($p['brand'] . ' ' . $p['name']) ?>">
          </button>
          <button class="frame-tile product-img-thumb"
                  data-img="<?= htmlspecialchars($p['img_ai']) ?>"
                  aria-label="AI Preview">
            <img src="<?= htmlspecialchars($p['img_ai']) ?>"
                 alt="AI Preview <?= htmlspecialchars($p['brand'] . ' ' . $p['name']) ?>">
          </button>
        </div>
        <?php endif; ?>
      <?php else: ?>
      <!-- AI model preview -->
      <div class="ai-tryon-card">
        <div class="ai-tryon-face" id="ai-face">
          <i data-lucide="glasses" class="ai-tryon-glasses" id="ai-glasses"
             style="color:<?= htmlspecialchars($p['color']) ?>"></i>
        </div>
        <div class="ai-tryon-footer">
          <span class="ai-badge">
            <i data-lucide="sparkles" style="width:11px;height:11px"></i> AI Preview
          </span>
          <span class="ai-tryon-name"><?= htmlspecialchars($p['brand']) ?> · <?= htmlspecialchars($p['name']) ?></span>
        </div>
      </div>

      <!-- Color thumbs -->
      <div class="product-thumbs">
        <?php foreach ($colors as $i => $c): ?>
          <button class="frame-tile ai-color-thumb<?= $i===0?' ai-color-thumb--active':'' ?>"
                  style="height:84px" data-color="<?= htmlspecialchars($c) ?>"
                  aria-label="<?= sprintf(htmlspecialchars($t['prod_color_aria']), $i+1) ?>">
            <i data-lucide="glasses" style="width:36px;height:36px;color:<?= $c ?>;stroke-width:1.3"></i>
          </button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="product-info">
      <div class="product-badges">
        <span class="badge badge--brand"><?= htmlspecialchars($p['brand']) ?></span>
        <?php if ($p['tag']): ?>
          <span class="badge badge--navy"><?= htmlspecialchars($p['tag']) ?></span>
        <?php endif; ?>
      </div>

      <h1 style="font-size:3rem;margin:.25rem 0"><?= htmlspecialchars($p['name']) ?></h1>
      <?php if (!empty($p['desc'])): ?>
        <p style="margin:.25rem 0 .75rem;color:var(--ink-500)"><?= htmlspecialchars($p['desc']) ?></p>
      <?php endif; ?>
      <div class="product-price"><?= htmlspecialchars($p['price']) ?> <span style="font-size:.95rem;font-weight:400;color:var(--ink-400)">mit MwSt.</span></div>
      <?php if (!empty($p['price_net'])): ?>
        <div style="font-size:.9rem;color:var(--ink-400);margin-top:-.5rem;margin-bottom:.75rem"><?= htmlspecialchars($p['price_net']) ?> ohne MwSt.</div>
      <?php endif; ?>

      <p class="lead" style="max-width:460px;margin-bottom:1.5rem">
        <?= htmlspecialchars($p['cat']) ?> · <?= htmlspecialchars($t['prod_studio_fit']) ?>
      </p>

      <!-- Color picker -->
      <div class="color-picker-wrap">
        <p class="color-picker-label"><?= htmlspecialchars($t['prod_color_label']) ?></p>
        <div class="color-picker">
          <?php foreach ($colors as $i => $c): ?>
            <button class="color-swatch<?= $i===0?' color-swatch--active':'' ?>"
                    style="background:<?= $c ?>"
                    aria-label="<?= sprintf(htmlspecialchars($t['prod_color_aria']), $i+1) ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- CTA -->
      <div class="product-actions">
        <form method="post">
          <input type="hidden" name="product_id" value="<?= htmlspecialchars($p['id']) ?>">
          <button type="submit" name="add_to_cart" class="btn btn--primary btn--lg">
            <i data-lucide="shopping-bag"></i> <?= htmlspecialchars($t['prod_add_cart']) ?>
          </button>
        </form>
        <button type="button" class="btn btn--secondary btn--lg" onclick="document.getElementById('studio-dialog').showModal()">
          <i data-lucide="calendar"></i> <?= htmlspecialchars($t['prod_book_studio']) ?>
        </button>
      </div>

      <?php if ($added): ?>
        <div class="alert alert--success">
          <i data-lucide="check-circle" style="flex-shrink:0"></i>
          <div>
            <strong><?= htmlspecialchars($t['prod_added_title']) ?></strong><br>
            <?= htmlspecialchars($t['prod_added_body']) ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($studio_booked): ?>
        <div class="alert alert--success">
          <i data-lucide="check-circle" style="flex-shrink:0"></i>
          <div>
            <strong><?= htmlspecialchars($t['book_success_title']) ?></strong><br>
            <?= htmlspecialchars($t['book_success_body']) ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="alert alert--navy">
        <i data-lucide="eye" style="flex-shrink:0"></i>
        <span><?= htmlspecialchars($t['prod_alert_lenses']) ?></span>
      </div>

      <!-- Tabs -->
      <div class="tabs-wrap">
        <div class="tabs" role="tablist">
          <?php foreach ($tabs as $key => $label): ?>
            <a href="?p=product&amp;id=<?= $p['id'] ?>&amp;tab=<?= $key ?>&amp;lang=<?= $lang ?>"
               class="tab<?= $tab===$key?' tab--active':'' ?>"
               role="tab"><?= htmlspecialchars($label) ?></a>
          <?php endforeach; ?>
        </div>
        <p class="tab-panel"><?= htmlspecialchars($panels[$tab] ?? $panels['desc']) ?></p>
      </div>
    </div>
  </div>
</div>

<!-- Studio booking dialog -->
<dialog id="studio-dialog" class="studio-dialog">
  <div class="studio-dialog__inner">
    <button class="studio-dialog__close" onclick="document.getElementById('studio-dialog').close()" aria-label="<?= htmlspecialchars($t['aria_close']) ?>">
      <i data-lucide="x"></i>
    </button>

    <div class="studio-dialog__header">
      <i data-lucide="calendar" class="studio-dialog__icon"></i>
      <h2><?= htmlspecialchars($t['prod_book_studio']) ?></h2>
      <p class="studio-dialog__product">
        <?= htmlspecialchars($p['brand'] . ' ' . $p['name']) ?>
      </p>
    </div>

    <?php if (!empty($studio_errors)): ?>
      <?php foreach ($studio_errors as $err): ?>
        <div class="alert alert--error" style="margin-bottom:.5rem">
          <i data-lucide="alert-circle"></i> <?= htmlspecialchars($err) ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" class="studio-dialog__form">
      <input type="hidden" name="studio_book" value="1">
      <input type="hidden" name="product_id" value="<?= htmlspecialchars($p['id']) ?>">

      <label class="booking-label">
        <?= htmlspecialchars($t['form_firstname']) ?> *
        <input type="text" name="s_name"
               value="<?= htmlspecialchars($_POST['s_name'] ?? '') ?>"
               class="booking-input" required
               placeholder="<?= htmlspecialchars($t['co_name_ph'] ?? '') ?>">
      </label>

      <label class="booking-label">
        <?= htmlspecialchars($t['form_email']) ?> *
        <input type="email" name="s_email"
               value="<?= htmlspecialchars($_POST['s_email'] ?? '') ?>"
               class="booking-input" required
               placeholder="<?= htmlspecialchars($t['co_email_ph'] ?? '') ?>">
      </label>

      <label class="booking-label">
        <?= htmlspecialchars($t['form_phone']) ?>
        <input type="tel" name="s_phone"
               value="<?= htmlspecialchars($_POST['s_phone'] ?? '') ?>"
               class="booking-input"
               placeholder="<?= htmlspecialchars($t['co_phone_ph'] ?? '') ?>">
      </label>

      <label class="booking-label" style="margin-bottom:1.25rem">
        <?= htmlspecialchars($t['form_date']) ?> *
        <input type="date" name="s_date"
               value="<?= htmlspecialchars($_POST['s_date'] ?? '') ?>"
               class="booking-input" required
               min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
      </label>

      <button type="submit" class="btn btn--primary btn--lg btn--block">
        <i data-lucide="calendar"></i> <?= htmlspecialchars($t['form_submit']) ?>
      </button>
    </form>
  </div>
</dialog>

<?php if (!empty($studio_errors)): ?>
<script>document.addEventListener('DOMContentLoaded', () => document.getElementById('studio-dialog').showModal());</script>
<?php endif; ?>
