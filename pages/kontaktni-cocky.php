<?php
/* Article: Contact lenses */
?>
<div class="page-wrap article-wrap">

  <a href="?p=home<?= lang_qs($lang) ?>" class="back-link">
    <i data-lucide="arrow-left"></i>
    <?= htmlspecialchars($t['nav_home']) ?>
  </a>

  <article class="article">

    <header class="article__header reveal">
      <p class="eyebrow">Expert OPTIC</p>
      <h1 class="article__title"><?= htmlspecialchars($t['kc_title']) ?></h1>
      <p class="lead"><?= htmlspecialchars($t['kc_lead']) ?></p>
      <img src="assets/contact-lenses/jdlj-3wtizqwr0hb6-us4lh.jpg"
           alt="Kontaktní čočky – barevné varianty"
           class="article__hero-img">
    </header>

    <!-- Pros & Cons -->
    <div class="cards-2 reveal" style="margin:2rem 0">

      <div class="card card--top-rule">
        <div class="article__card-head">
          <i data-lucide="thumbs-up" class="service-icon" style="color:var(--brand)"></i>
          <h2><?= htmlspecialchars($t['kc_pros_title']) ?></h2>
        </div>
        <ul class="checkpoint-list">
          <li>
            <i data-lucide="check-circle" class="checkpoint-list__icon"></i>
            <?= htmlspecialchars($t['kc_pros_1']) ?>
          </li>
        </ul>
      </div>

      <div class="card card--top-rule">
        <div class="article__card-head">
          <i data-lucide="thumbs-down" class="service-icon" style="color:var(--ink-400)"></i>
          <h2><?= htmlspecialchars($t['kc_cons_title']) ?></h2>
        </div>
        <ul class="checkpoint-list">
          <li>
            <i data-lucide="alert-circle" class="checkpoint-list__icon" style="color:var(--ink-400)"></i>
            <?= htmlspecialchars($t['kc_cons_1']) ?>
          </li>
          <li>
            <i data-lucide="alert-circle" class="checkpoint-list__icon" style="color:var(--ink-400)"></i>
            <?= htmlspecialchars($t['kc_cons_2']) ?>
          </li>
        </ul>
      </div>

    </div>

    <!-- Types -->
    <section class="reveal">
      <img src="assets/contact-lenses/jdlj-7fqj6v5gb164-k3d3p.jpg"
           alt="Kontaktní čočky na prstu"
           class="article__section-img">
      <h2 class="article__section-title"><?= htmlspecialchars($t['kc_types_title']) ?></h2>

      <div class="cards-3" style="margin-top:1.25rem">

        <div class="card card--top-rule">
          <i data-lucide="layers" class="service-icon"></i>
          <h3><?= htmlspecialchars($t['kc_hard_title']) ?></h3>
          <p><?= htmlspecialchars($t['kc_hard_body']) ?></p>
        </div>

        <div class="card card--top-rule">
          <i data-lucide="sun" class="service-icon"></i>
          <h3><?= htmlspecialchars($t['kc_daily_title']) ?></h3>
          <p><?= htmlspecialchars($t['kc_daily_body']) ?></p>
        </div>

        <div class="card card--top-rule">
          <i data-lucide="calendar" class="service-icon"></i>
          <h3><?= htmlspecialchars($t['kc_14day_title']) ?></h3>
          <p><?= htmlspecialchars($t['kc_14day_body']) ?></p>
        </div>

      </div>

      <div class="cards-3" style="margin-top:1.25rem">

        <div class="card card--top-rule">
          <i data-lucide="moon" class="service-icon"></i>
          <h3><?= htmlspecialchars($t['kc_month_title']) ?></h3>
          <p><?= htmlspecialchars($t['kc_month_body']) ?></p>
        </div>

      </div>
    </section>

    <!-- CTA -->
    <div class="cta-band reveal" style="margin-top:2.5rem;border-radius:var(--radius-lg)">
      <div class="cta-band__inner">
        <div>
          <h2 class="cta-band__headline"><?= htmlspecialchars($t['kc_cta_headline']) ?></h2>
          <p class="cta-band__sub"><?= htmlspecialchars($t['kc_cta_sub']) ?></p>
        </div>
        <div class="cta-band__actions">
          <a href="?p=booking<?= lang_qs($lang) ?>" class="btn btn--primary btn--lg">
            <i data-lucide="calendar"></i> <?= htmlspecialchars($t['kc_cta_book']) ?>
          </a>
          <a href="tel:+420603419882" class="btn btn--outline-light btn--lg">
            <i data-lucide="phone"></i> +420 603 419 882
          </a>
        </div>
      </div>
    </div>

  </article>
</div>
