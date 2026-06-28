<?php
/* Article: Sports eyewear */
?>
<div class="page-wrap article-wrap">

  <a href="?p=home<?= lang_qs($lang) ?>" class="back-link">
    <i data-lucide="arrow-left"></i>
    <?= htmlspecialchars($t['nav_home']) ?>
  </a>

  <article class="article">

    <header class="article__header reveal">
      <p class="eyebrow">Expert OPTIC</p>
      <h1 class="article__title"><?= htmlspecialchars($t['sb_title']) ?></h1>
      <p class="lead"><?= htmlspecialchars($t['sb_lead']) ?></p>
    </header>

    <!-- Intro -->
    <div class="card card--top-rule reveal" style="margin-bottom:2rem">
      <i data-lucide="zap" class="service-icon"></i>
      <p style="margin:.75rem 0 .5rem"><?= htmlspecialchars($t['sb_intro_p1']) ?></p>
      <p style="margin:0">
        <?= htmlspecialchars($t['sb_intro_p2']) ?>
        <strong><?= htmlspecialchars($t['sb_intro_warning']) ?></strong>
      </p>
    </div>

    <!-- Photo gallery -->
    <div class="article__gallery reveal">
      <img src="assets/sport-glasses/jdlj-572tv5eoo4vg-fkdef.jpg"  alt="Sportovní brýle">
      <img src="assets/sport-glasses/jdlj-573g737i23zg-q1lkm.jpg"  alt="Sportovní brýle">
      <img src="assets/sport-glasses/jdlj-5xa9sw54v0z0-w9lts.jpg"  alt="Sportovní brýle">
      <img src="assets/sport-glasses/jdlj-5xc8cbhau10s-vw1q1.jpg"  alt="Sportovní brýle">
      <img src="assets/sport-glasses/jdlj-5xedm7aq8o8c-9h3g8.jpg"  alt="Sportovní brýle">
      <img src="assets/sport-glasses/jdlj-5xfa87iivd7w-xp7kn.jpg"  alt="Sportovní brýle">
      <img src="assets/sport-glasses/jdlj-5xg1istqsg4s-ujsjx.jpg"  alt="Sportovní brýle">
      <img src="assets/sport-glasses/jdlj-5xifay3aj9v0-o2ohr.jpg"  alt="Sportovní brýle">
      <img src="assets/sport-glasses/jdlj-5xjq3e1ye9po-iko8z.jpg"  alt="Sportovní brýle">
      <img src="assets/sport-glasses/jdlj-5xl79j3piegc-koivb.jpg"  alt="Sportovní brýle">
    </div>

    <!-- Cycling -->
    <section class="reveal">
      <h2 class="article__section-title">
        <i data-lucide="bike" style="vertical-align:middle;margin-right:.5rem;color:var(--brand)"></i>
        <?= htmlspecialchars($t['sb_cycling_title']) ?>
      </h2>
      <div class="card card--top-rule" style="margin-top:1rem">
        <p style="margin:0 0 1rem"><?= htmlspecialchars($t['sb_cycling_intro']) ?></p>
        <ul class="checkpoint-list">
          <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_cycling_1']) ?></li>
          <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_cycling_2']) ?></li>
          <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_cycling_3']) ?></li>
        </ul>
      </div>
    </section>

    <!-- Ski -->
    <section class="reveal" style="margin-top:2.5rem">
      <h2 class="article__section-title">
        <i data-lucide="mountain-snow" style="vertical-align:middle;margin-right:.5rem;color:var(--brand)"></i>
        <?= htmlspecialchars($t['sb_ski_title']) ?>
      </h2>
      <div class="cards-2" style="margin-top:1rem">
        <div class="card card--top-rule">
          <p style="margin:0 0 1rem"><?= htmlspecialchars($t['sb_ski_body']) ?></p>
          <p style="margin:0;color:var(--ink-600);font-size:.9375rem"><?= htmlspecialchars($t['sb_ski_note']) ?></p>
        </div>
        <div class="card card--top-rule">
          <ul class="checkpoint-list">
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_ski_1']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_ski_2']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_ski_3']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_ski_4']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_ski_5']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_ski_6']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_ski_7']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_ski_8']) ?></li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Golf -->
    <section class="reveal" style="margin-top:2.5rem">
      <h2 class="article__section-title">
        <i data-lucide="target" style="vertical-align:middle;margin-right:.5rem;color:var(--brand)"></i>
        <?= htmlspecialchars($t['sb_golf_title']) ?>
      </h2>
      <div class="cards-2" style="margin-top:1rem">
        <div class="card card--top-rule">
          <p style="margin:0 0 .75rem;font-size:1.0625rem;font-weight:600"><?= htmlspecialchars($t['sb_golf_pitch']) ?></p>
          <p style="margin:0;color:var(--ink-600);font-size:.9375rem"><?= htmlspecialchars($t['sb_golf_body']) ?></p>
        </div>
        <div class="card card--top-rule">
          <ul class="checkpoint-list">
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_golf_1']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_golf_2']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_golf_3']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_golf_4']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_golf_5']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_golf_6']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_golf_7']) ?></li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Running -->
    <section class="reveal" style="margin-top:2.5rem">
      <h2 class="article__section-title">
        <i data-lucide="footprints" style="vertical-align:middle;margin-right:.5rem;color:var(--brand)"></i>
        <?= htmlspecialchars($t['sb_run_title']) ?>
      </h2>
      <div class="cards-2" style="margin-top:1rem">
        <div class="card card--top-rule">
          <p style="margin:0 0 .75rem;font-size:1.0625rem;font-weight:600"><?= htmlspecialchars($t['sb_run_pitch']) ?></p>
          <p style="margin:0;color:var(--ink-600);font-size:.9375rem"><?= htmlspecialchars($t['sb_run_body']) ?></p>
        </div>
        <div class="card card--top-rule">
          <ul class="checkpoint-list">
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_run_1']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_run_2']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_run_3']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_run_4']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_run_5']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_run_6']) ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= htmlspecialchars($t['sb_run_7']) ?></li>
          </ul>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <div class="cta-band reveal" style="margin-top:2.5rem;border-radius:var(--radius-lg)">
      <div class="cta-band__inner">
        <div>
          <h2 class="cta-band__headline"><?= htmlspecialchars($t['sb_cta_headline']) ?></h2>
          <p class="cta-band__sub"><?= htmlspecialchars($t['sb_cta_sub']) ?></p>
        </div>
        <div class="cta-band__actions">
          <a href="?p=booking<?= lang_qs($lang) ?>" class="btn btn--primary btn--lg">
            <i data-lucide="calendar"></i> <?= htmlspecialchars($t['sb_cta_book']) ?>
          </a>
          <a href="tel:+420603419882" class="btn btn--outline-light btn--lg">
            <i data-lucide="phone"></i> +420 603 419 882
          </a>
        </div>
      </div>
    </div>

  </article>
</div>
