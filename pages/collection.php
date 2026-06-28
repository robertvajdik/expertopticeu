<?php
$filtered = ($cat === 'Alle') ? $products : array_values(array_filter($products, fn($p) => $p['cat'] === $cat));
$count    = count($filtered);
$sort     = $_GET['sort'] ?? 'recommended';
?>

<div class="page-wrap">
  <p class="eyebrow"><?= htmlspecialchars($t['coll_eyebrow']) ?></p>
  <h1><?= $t['coll_headline'] ?></h1>
  <p class="lead" style="max-width:560px;margin-bottom:2rem">
    <?= htmlspecialchars($t['coll_lead']) ?>
  </p>

  <!-- Filter bar -->
  <div class="filter-bar">
    <div class="filter-pills">
      <?php
      $cat_keys = [
        'Alle'            => 'cat_all',
        'Optische Brillen'=> 'cat_optical',
        'Sonnenbrillen'   => 'cat_sun',
        'Sportbrillen'    => 'cat_sport',
        'Lesebrillen'     => 'cat_reading',
      ];
      $categories = array_keys($cat_keys);
      foreach ($categories as $c): ?>
        <a href="?p=collection&amp;cat=<?= urlencode($c) ?>&amp;lang=<?= $lang ?>"
           class="pill<?= $c === $cat ? ' pill--active' : '' ?>">
          <?= htmlspecialchars($t[$cat_keys[$c]] ?? $c) ?>
        </a>
      <?php endforeach; ?>
    </div>
    <form method="get" class="sort-form">
      <input type="hidden" name="p" value="collection">
      <input type="hidden" name="cat" value="<?= htmlspecialchars($cat) ?>">
      <input type="hidden" name="lang" value="<?= $lang ?>">
      <select name="sort" onchange="this.form.submit()" class="select">
        <option value="recommended" <?= $sort==='recommended'?'selected':'' ?>><?= htmlspecialchars($t['sort_recommended']) ?></option>
        <option value="price_asc"   <?= $sort==='price_asc'  ?'selected':'' ?>><?= htmlspecialchars($t['sort_price_asc']) ?></option>
        <option value="price_desc"  <?= $sort==='price_desc' ?'selected':'' ?>><?= htmlspecialchars($t['sort_price_desc']) ?></option>
        <option value="newest"      <?= $sort==='newest'     ?'selected':'' ?>><?= htmlspecialchars($t['sort_newest']) ?></option>
      </select>
    </form>
  </div>

  <p class="collection-count"><?= sprintf(htmlspecialchars($t['count_models']), $count) ?></p>

  <div class="cards-4">
    <?php foreach ($filtered as $i => $p): ?>
      <a href="?p=product&amp;id=<?= $p['id'] ?>&amp;lang=<?= $lang ?>" class="product-card reveal" style="transition-delay:<?= ($i % 4) * 55 ?>ms">
        <div class="product-card__img-wrap">
          <?php if (!empty($p['img'])): ?>
            <div class="frame-tile" style="height:190px;padding:0;overflow:hidden">
              <img src="<?= htmlspecialchars($p['img']) ?>"
                   alt="<?= htmlspecialchars($p['brand'] . ' ' . $p['name']) ?>"
                   style="width:100%;height:100%;object-fit:cover;display:block">
            </div>
          <?php else: ?>
          <div class="frame-tile" style="height:190px">
            <i data-lucide="glasses" style="width:60px;height:60px;color:<?= $p['color'] ?>;opacity:.34;stroke-width:1.2"></i>
            <span class="photo-placeholder"><?= htmlspecialchars($t['photo_coming']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($p['tag']): ?>
            <span class="badge badge--brand img-badge"><?= htmlspecialchars($p['tag']) ?></span>
          <?php endif; ?>
          <div class="product-card__hover-cta"><?= htmlspecialchars($t['product_view']) ?></div>
        </div>
        <div class="product-card__body">
          <div class="product-card__brand"><?= htmlspecialchars($p['brand']) ?></div>
          <div class="product-card__name"><?= htmlspecialchars($p['name']) ?></div>
          <div class="product-card__row">
            <span class="product-card__price"><?= htmlspecialchars($p['price']) ?></span>
            <span class="product-card__cat"><?= htmlspecialchars($t[$cat_keys[$p['cat']] ?? 'cat_all'] ?? $p['cat']) ?></span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
