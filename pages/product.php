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
        <a href="?p=booking&amp;lang=<?= $lang ?>" class="btn btn--secondary btn--lg"><?= htmlspecialchars($t['prod_book_studio']) ?></a>
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
