<?php
$featured = array_slice($products, 0, 4);
$brands = [
  ['name' => 'IC! Berlin',       'logo' => null,                              'url' => 'https://www.ic-berlin.de/'],
  ['name' => 'Etnia Barcelona',  'logo' => 'assets/brands/etnia-barcelona.svg','url' => 'https://www.etniabarcelona.com/'],
  ['name' => 'Blackfin',         'logo' => 'assets/brands/blackfin.png',       'url' => 'https://www.blackfin.com/'],
  ['name' => 'Serengeti',        'logo' => null,                              'url' => 'https://www.serengetieyewear.com/'],
  ['name' => 'Gloryfy',          'logo' => 'assets/brands/gloryfy.svg',        'url' => 'https://www.gloryfy.com/'],
  ['name' => 'Vuarnet',          'logo' => 'assets/brands/vuarnet.png',        'url' => 'https://www.vuarnet.com/'],
  ['name' => 'Nannini',          'logo' => 'assets/brands/nannini.png',        'url' => 'https://www.nannini.it/'],
  ['name' => 'Silhouette',       'logo' => null,                              'url' => 'https://www.silhouette.com/'],
];
$services = [
  ['icon'=>'eye',     'title'=>$t['svc_1_title'], 'body'=>$t['svc_1_body']],
  ['icon'=>'glasses', 'title'=>$t['svc_2_title'], 'body'=>$t['svc_2_body']],
  ['icon'=>'hand',    'title'=>$t['svc_3_title'], 'body'=>$t['svc_3_body']],
];
?>

<!-- Hero -->
<section class="hero">
  <div class="hero__content reveal">
    <p class="eyebrow"><?= htmlspecialchars($t['hero_eyebrow']) ?></p>
    <h1 class="hero__headline">
      <?= htmlspecialchars($t['hero_headline_1']) ?><br>
      <?= htmlspecialchars($t['hero_headline_2']) ?> <span class="script"><?= htmlspecialchars($t['hero_script']) ?></span>.
    </h1>
    <p class="lead">
      <?= htmlspecialchars($t['hero_lead']) ?>
    </p>
    <div class="hero__cta">
      <a href="?p=booking<?= lang_qs($lang) ?>" class="btn btn--primary btn--lg">
        <i data-lucide="calendar"></i> <?= htmlspecialchars($t['hero_cta_book']) ?>
      </a>
      <a href="?p=collection<?= lang_qs($lang) ?>" class="btn btn--secondary btn--lg"><?= htmlspecialchars($t['hero_cta_coll']) ?></a>
    </div>
    <div class="hero__stats">
      <div class="hero__stat">
        <span class="stat-value">1000+</span>
        <span class="stat-label"><?= htmlspecialchars($t['stat_models']) ?></span>
      </div>
      <div class="hero__stat-divider"></div>
      <div class="hero__stat">
        <span class="stat-value"><?= htmlspecialchars($t['stat_since']) ?></span>
        <span class="stat-label"><?= htmlspecialchars($t['stat_master']) ?></span>
      </div>
      <div class="hero__stat-divider"></div>
      <div class="hero__stat">
        <span class="stat-value"><?= htmlspecialchars($t['stat_free']) ?></span>
        <span class="stat-label"><?= htmlspecialchars($t['stat_eye_test']) ?></span>
      </div>
    </div>
  </div>

  <div class="hero__visual">
    <div class="hero__img-wrap">
      <img src="assets/cxzg-5w6yeh8y9huv-i2vj2.jpg" alt="<?= htmlspecialchars($t['alt_studio_interior']) ?>" class="hero__photo">
    </div>
    <div class="hero__badge">
      <i data-lucide="badge-check" class="hero__badge-icon"></i>
      <div>
        <strong><?= htmlspecialchars($t['badge_checkpoint']) ?></strong>
        <span><?= htmlspecialchars($t['badge_free_test']) ?></span>
      </div>
    </div>
  </div>
</section>

<!-- Happy Hour promo -->
<div class="happy-hour-bar">
  <div class="happy-hour-bar__inner">
    <i data-lucide="clock" class="happy-hour-bar__icon"></i>
    <div>
      <strong>HAPPY HOUR</strong> — <?= $t['happy_hour_text'] ?>
    </div>
    <a href="?p=booking<?= lang_qs($lang) ?>" class="btn btn--outline-light btn--sm"><?= htmlspecialchars($t['happy_hour_btn']) ?></a>
  </div>
</div>

<!-- Checkpoint Auge -->
<section class="checkpoint-section reveal">
  <div class="checkpoint-section__inner">

    <div class="checkpoint-seal">
      <img src="assets/checkpointauge.png" alt="Lizenzierter Checkpoint Auge" class="checkpoint-seal__img">
    </div>

    <div class="checkpoint-content">
      <p class="eyebrow"><?= htmlspecialchars($t['cp_eyebrow']) ?></p>
      <h2 class="checkpoint-headline"><?= htmlspecialchars($t['cp_headline']) ?></h2>
      <p class="lead checkpoint-lead">
        <?= htmlspecialchars($t['cp_lead']) ?>
      </p>
      <ul class="checkpoint-list">
        <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['cp_bullet_1']) ?></li>
        <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['cp_bullet_2']) ?></li>
        <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['cp_bullet_3']) ?></li>
        <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['cp_bullet_4']) ?></li>
      </ul>
      <a href="?p=booking<?= lang_qs($lang) ?>" class="btn btn--primary btn--lg checkpoint-cta">
        <i data-lucide="calendar"></i> <?= htmlspecialchars($t['cp_cta']) ?>
      </a>
    </div>

    <div class="checkpoint-photo">
      <img src="assets/cxzg-1t1fajpevtoe-5hxdr.jpg"
           alt="<?= htmlspecialchars($t['alt_checkpoint']) ?>"
           class="checkpoint-photo__img"
           onerror="this.closest('.checkpoint-photo').style.display='none'">
    </div>

  </div>
</section>

<!-- Services -->
<section class="section-wrap">
  <div class="cards-3">
    <?php foreach ($services as $i => $s): ?>
      <div class="card card--top-rule reveal" style="transition-delay:<?= $i * 80 ?>ms">
        <i data-lucide="<?= $s['icon'] ?>" class="service-icon"></i>
        <h3><?= htmlspecialchars($s['title']) ?></h3>
        <p><?= htmlspecialchars($s['body']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Featured products -->
<section class="section-wrap">
  <div class="section-head reveal">
    <div>
      <p class="eyebrow"><?= htmlspecialchars($t['feat_eyebrow']) ?></p>
      <h2><?= htmlspecialchars($t['feat_headline']) ?></h2>
    </div>
    <a href="?p=collection<?= lang_qs($lang) ?>" class="btn btn--ghost btn--sm">
      <?= htmlspecialchars($t['feat_view_all']) ?> <i data-lucide="arrow-right"></i>
    </a>
  </div>
  <div class="cards-4">
    <?php foreach ($featured as $i => $p): ?>
      <a href="?p=product&amp;id=<?= $p['id'] ?><?= lang_qs($lang) ?>" class="product-card reveal" style="transition-delay:<?= $i * 60 ?>ms">
        <div class="product-card__img-wrap">
          <div class="frame-tile" style="height:200px">
            <i data-lucide="glasses" style="color:<?= $p['color'] ?>" class="frame-icon"></i>
            <span class="photo-placeholder"><?= htmlspecialchars($t['photo_coming']) ?></span>
          </div>
          <?php if ($p['tag']): ?>
            <span class="badge badge--brand img-badge"><?= htmlspecialchars($p['tag']) ?></span>
          <?php endif; ?>
          <div class="product-card__hover-cta"><?= htmlspecialchars($t['product_view']) ?></div>
        </div>
        <div class="product-card__body">
          <div class="product-card__brand"><?= htmlspecialchars($p['brand']) ?></div>
          <div class="product-card__name"><?= htmlspecialchars($p['name']) ?></div>
          <div class="product-card__price"><?= htmlspecialchars(fmt_price_display($p['price'], $lang)) ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- Brands -->
<section class="brands-band reveal" id="marken">
  <div class="brands-band__inner">
    <?php foreach ($brands as $b): ?>
      <a href="<?= htmlspecialchars($b['url']) ?>" target="_blank" rel="noopener" class="brand-item" aria-label="<?= htmlspecialchars($b['name']) ?>">
        <?php if ($b['logo']): ?>
          <img src="<?= htmlspecialchars($b['logo']) ?>" alt="<?= htmlspecialchars($b['name']) ?>" class="brand-logo">
        <?php else: ?>
          <span class="brand-name"><?= htmlspecialchars($b['name']) ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- Craft / about -->
<section class="section-wrap craft-band">
  <div class="craft-band__img">
    <img src="assets/storefront.jpg" alt="<?= htmlspecialchars($t['alt_storefront']) ?>" class="craft-band__photo">
  </div>
  <div class="craft-band__text reveal">
    <p class="eyebrow"><?= htmlspecialchars($t['craft_eyebrow']) ?></p>
    <h2><?= htmlspecialchars($t['craft_headline']) ?></h2>
    <p class="lead">
      <?= htmlspecialchars($t['craft_lead']) ?>
    </p>
    <div class="master-quote">
      <img src="assets/cxzg-3kkk19yomwek-6rgo8.jpg" alt="<?= htmlspecialchars($t['alt_master']) ?>" class="master-quote__photo">
      <div>
        <blockquote class="craft-quote">
          <?= htmlspecialchars($t['craft_quote']) ?>
        </blockquote>
        <p class="craft-attribution"><?= htmlspecialchars($t['craft_attribution']) ?></p>
      </div>
    </div>
    <a href="?p=booking<?= lang_qs($lang) ?>" class="btn btn--secondary">
      <i data-lucide="calendar"></i> <?= $t['craft_cta'] ?>
    </a>
  </div>
</section>

<!-- CTA band -->
<section class="cta-band reveal">
  <div class="cta-band__inner">
    <div>
      <h2 class="cta-band__headline"><?= htmlspecialchars($t['cta_headline']) ?></h2>
      <p class="cta-band__sub"><?= htmlspecialchars($t['cta_sub']) ?></p>
    </div>
    <div class="cta-band__actions">
      <a href="?p=booking<?= lang_qs($lang) ?>" class="btn btn--primary btn--lg">
        <i data-lucide="calendar"></i> <?= htmlspecialchars($t['cta_online']) ?>
      </a>
      <a href="tel:+4318150558" class="btn btn--outline-light btn--lg">
        <i data-lucide="phone"></i> <?= htmlspecialchars($t['cta_phone']) ?>
      </a>
    </div>
  </div>
</section>
