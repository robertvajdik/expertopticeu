<?php
/* Article: Sports eyewear */
$T = fn(string $k) => htmlspecialchars($t[$k] ?? '');
?>
<div class="page-wrap article-wrap">

  <a href="?p=home<?= lang_qs($lang) ?>" class="back-link">
    <i data-lucide="arrow-left"></i>
    <?= $T('nav_home') ?>
  </a>

  <article class="article">

    <header class="article__header reveal">
      <p class="eyebrow">Expert OPTIC</p>
      <h1 class="article__title"><?= $T('sb_title') ?></h1>
      <p class="lead"><?= $T('sb_lead') ?></p>
      <img src="assets/sport-glasses/jdlj-5xc8cbhau10s-vw1q1.jpg"
           alt="Adidas evil eye halfrim pro – Red Dot Design Award"
           class="article__hero-img">
    </header>

    <!-- Intro -->
    <div class="card card--top-rule reveal" style="margin-bottom:2.5rem">
      <i data-lucide="zap" class="service-icon"></i>
      <p style="margin:.75rem 0 .5rem"><?= $T('sb_intro_p1') ?></p>
      <p style="margin:0">
        <?= $T('sb_intro_p2') ?>
        <strong><?= $T('sb_intro_warning') ?></strong>
      </p>
    </div>

    <!-- Cycling -->
    <section class="reveal">
      <h2 class="article__section-title">
        <i data-lucide="bike" style="vertical-align:middle;margin-right:.5rem;color:var(--brand)"></i>
        <?= $T('sb_cycling_title') ?>
      </h2>
      <div class="article__img-text reveal" style="margin-top:1rem">
        <img src="assets/sport-glasses/jdlj-5xa9sw54v0z0-w9lts.jpg"
             alt="Sportovní brýle pro cyklistiku">
        <div class="card card--top-rule">
          <p style="margin:0 0 1rem"><?= $T('sb_cycling_intro') ?></p>
          <ul class="checkpoint-list">
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_cycling_1') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_cycling_2') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_cycling_3') ?></li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Ski -->
    <section class="reveal" style="margin-top:2.5rem">
      <h2 class="article__section-title">
        <i data-lucide="mountain-snow" style="vertical-align:middle;margin-right:.5rem;color:var(--brand)"></i>
        <?= $T('sb_ski_title') ?>
      </h2>

      <div class="article__img-text reveal" style="margin-top:1rem">
        <img src="assets/sport-glasses/jdlj-572tv5eoo4vg-fkdef.jpg"
             alt="Lyžařské brýle se svorkou pro dioptrická skla">
        <div class="card card--top-rule">
          <p style="margin:0 0 1rem"><?= $T('sb_ski_body') ?></p>
          <p style="margin:0;color:var(--ink-600);font-size:.9375rem"><?= $T('sb_ski_note') ?></p>
        </div>
      </div>

      <div class="cards-3 reveal" style="margin-top:1rem">
        <div class="article__img-card">
          <img src="assets/sport-glasses/jdlj-5xedm7aq8o8c-9h3g8.jpg"
               alt="ClimaCool větrací systém">
        </div>
        <div class="article__img-card">
          <img src="assets/sport-glasses/jdlj-5xg1istqsg4s-ujsjx.jpg"
               alt="Svorka pro dioptrická skla – ilustrace">
        </div>
        <div class="card card--top-rule">
          <ul class="checkpoint-list" style="margin:0">
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_ski_1') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_ski_2') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_ski_3') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_ski_4') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_ski_5') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_ski_6') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_ski_7') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_ski_8') ?></li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Golf -->
    <section class="reveal" style="margin-top:2.5rem">
      <h2 class="article__section-title">
        <i data-lucide="target" style="vertical-align:middle;margin-right:.5rem;color:var(--brand)"></i>
        <?= $T('sb_golf_title') ?>
      </h2>

      <div class="article__img-text reveal" style="margin-top:1rem">
        <img src="assets/sport-glasses/jdlj-5xl79j3piegc-koivb.jpg"
             alt="Golfové brýle – výměnná skla">
        <div class="card card--top-rule">
          <p style="margin:0 0 .75rem;font-size:1.0625rem;font-weight:600"><?= $T('sb_golf_pitch') ?></p>
          <p style="margin:0;color:var(--ink-600);font-size:.9375rem"><?= $T('sb_golf_body') ?></p>
        </div>
      </div>

      <div class="cards-3 reveal" style="margin-top:1rem">
        <div class="article__img-card">
          <img src="assets/sport-glasses/jdlj-5xifay3aj9v0-o2ohr.jpg"
               alt="Svorka pro dioptrická skla – golf">
        </div>
        <div class="article__img-card">
          <img src="assets/sport-glasses/jdlj-5xfa87iivd7w-xp7kn.jpg"
               alt="Golfové brýle s tvrzeným pouzdrem">
        </div>
        <div class="card card--top-rule">
          <ul class="checkpoint-list" style="margin:0">
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_golf_1') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_golf_2') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_golf_3') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_golf_4') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_golf_5') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_golf_6') ?></li>
            <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_golf_7') ?></li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Running -->
    <section class="reveal" style="margin-top:2.5rem">
      <h2 class="article__section-title">
        <i data-lucide="footprints" style="vertical-align:middle;margin-right:.5rem;color:var(--brand)"></i>
        <?= $T('sb_run_title') ?>
      </h2>

      <div class="article__img-text reveal" style="margin-top:1rem">
        <img src="assets/sport-glasses/jdlj-573g737i23zg-q1lkm.jpg"
             alt="Brýle na běhání">
        <div>
          <div class="card card--top-rule" style="margin-bottom:1rem">
            <p style="margin:0 0 .5rem;font-size:1.0625rem;font-weight:600"><?= $T('sb_run_pitch') ?></p>
            <p style="margin:0;color:var(--ink-600);font-size:.9375rem"><?= $T('sb_run_body') ?></p>
          </div>
          <div class="card card--top-rule">
            <ul class="checkpoint-list" style="margin:0">
              <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_run_1') ?></li>
              <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_run_2') ?></li>
              <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_run_3') ?></li>
              <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_run_4') ?></li>
              <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_run_5') ?></li>
              <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_run_6') ?></li>
              <li><i data-lucide="check-circle" class="checkpoint-list__icon"></i> <?= $T('sb_run_7') ?></li>
            </ul>
          </div>
        </div>
      </div>

      <div class="article__img-card reveal" style="margin-top:1rem;max-width:320px">
        <img src="assets/sport-glasses/jdlj-5xjq3e1ye9po-iko8z.jpg"
             alt="Sportovní brýle na běhání">
      </div>
    </section>

    <!-- CTA -->
    <div class="cta-band reveal" style="margin-top:2.5rem;border-radius:var(--radius-lg)">
      <div class="cta-band__inner">
        <div>
          <h2 class="cta-band__headline"><?= $T('sb_cta_headline') ?></h2>
          <p class="cta-band__sub"><?= $T('sb_cta_sub') ?></p>
        </div>
        <div class="cta-band__actions">
          <a href="?p=booking<?= lang_qs($lang) ?>" class="btn btn--primary btn--lg">
            <i data-lucide="calendar"></i> <?= $T('sb_cta_book') ?>
          </a>
          <a href="tel:+420603419882" class="btn btn--outline-light btn--lg">
            <i data-lucide="phone"></i> +420 603 419 882
          </a>
        </div>
      </div>
    </div>

  </article>
</div>
