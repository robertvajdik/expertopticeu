<?php
/* Brno studio — opening hours logic
   Wed=3, Thu=4: 12:30-18:00   Fri=5: 11:30-17:00   All else: closed */
$dow_brno   = (int) date('N');
$open_brno  = in_array($dow_brno, [3, 4]);
$fri_brno   = $dow_brno === 5;
?>

<div class="page-wrap">
  <p class="eyebrow"><?= htmlspecialchars($t['brno_eyebrow']) ?></p>
  <h1><?= htmlspecialchars($t['brno_headline']) ?></h1>
  <p class="lead booking-lead"><?= htmlspecialchars($t['brno_lead']) ?></p>

  <div class="booking-grid">

    <!-- ── Main content ── -->
    <div class="brno-main">

      <!-- Studio intro -->
      <div class="card brno-card">
        <p class="brno-subtitle"><?= htmlspecialchars($t['brno_subtitle']) ?></p>
        <p><?= htmlspecialchars($t['brno_intro']) ?></p>
        <p class="brno-cta-text"><strong><?= htmlspecialchars($t['brno_cta']) ?></strong></p>

        <div class="brno-photos">
          <img src="assets/eo-ueu.jpg"
               alt="<?= htmlspecialchars($t['alt_brno_interior']) ?>"
               class="brno-photo" loading="lazy">
        </div>

        <a href="?p=booking&amp;lang=<?= $lang ?>" class="btn btn--primary">
          <?= htmlspecialchars($t['brno_cta_btn']) ?>
        </a>
      </div>

      <!-- Children eye screening -->
      <div class="card brno-card brno-card--children reveal">
        <p class="eyebrow"><?= htmlspecialchars($t['brno_children_eyebrow']) ?></p>
        <h2><?= htmlspecialchars($t['brno_children_headline']) ?></h2>
        <p><?= htmlspecialchars($t['brno_children_lead']) ?></p>

        <img src="assets/schulkinder2.JPG"
             alt="<?= htmlspecialchars($t['alt_brno_children']) ?>"
             class="brno-children-photo" loading="lazy">

        <ul class="brno-equipment">
          <li><i data-lucide="check" aria-hidden="true"></i><?= htmlspecialchars($t['brno_equip_1']) ?></li>
          <li><i data-lucide="check" aria-hidden="true"></i><?= htmlspecialchars($t['brno_equip_2']) ?></li>
          <li><i data-lucide="check" aria-hidden="true"></i><?= htmlspecialchars($t['brno_equip_3']) ?></li>
          <li><i data-lucide="check" aria-hidden="true"></i><?= htmlspecialchars($t['brno_equip_4']) ?></li>
          <li><i data-lucide="check" aria-hidden="true"></i><?= htmlspecialchars($t['brno_equip_5']) ?></li>
        </ul>
      </div>

    </div><!-- /brno-main -->

    <!-- ── Sidebar ── -->
    <div class="booking-sidebar">

      <!-- Address -->
      <div class="card booking-info-card">
        <div class="card-heading">
          <i data-lucide="map-pin" class="card-heading-icon"></i>
          <h3><?= htmlspecialchars($t['brno_sidebar_studio']) ?></h3>
        </div>
        <img src="assets/expertoptic-brno.jpg"
             alt="<?= htmlspecialchars($t['alt_brno_studio']) ?>"
             class="storefront-thumb" loading="lazy">
        <address class="studio-address">
          Hlavní 131<br>
          624 00 Brno-Komín<br>
          Česká republika
        </address>
        <a href="tel:+420603419882" class="studio-phone">+420 603 419 882</a>
        <a href="mailto:brno@tstoptik.com" class="studio-email">brno@tstoptik.com</a>
        <a href="https://www.expertoptic.eu" target="_blank" rel="noopener" class="studio-email">www.expertoptic.eu</a>
        <div class="studio-map">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2607.0!2d16.5544!3d49.2155!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4712948c4e9f2b27%3A0x0!2sHlavn%C3%AD+131%2C+Brno-Kom%C3%ADn!5e0!3m2!1scs!2scz!4v1"
            width="100%" height="200" style="border:0" allowfullscreen loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="Expert OPTIC Brno-Komín — Hlavní 131"></iframe>
        </div>
      </div>

      <!-- Opening hours -->
      <div class="card booking-info-card">
        <div class="card-heading">
          <i data-lucide="clock" class="card-heading-icon"></i>
          <h3><?= htmlspecialchars($t['brno_hours_label']) ?></h3>
        </div>
        <div class="hours-row hours-row--border">
          <span><?= htmlspecialchars($t['brno_hours_wed_thu']) ?></span>
          <span class="hours-time"><?= htmlspecialchars($t['brno_hours_wed_thu_time']) ?></span>
        </div>
        <div class="hours-row hours-row--border">
          <span><?= htmlspecialchars($t['brno_hours_fri']) ?></span>
          <span class="hours-time"><?= htmlspecialchars($t['brno_hours_fri_time']) ?></span>
        </div>
        <div class="hours-row">
          <span><?= htmlspecialchars($t['brno_hours_other_label']) ?></span>
          <span class="hours-closed"><?= htmlspecialchars($t['brno_hours_other_val']) ?></span>
        </div>
        <div class="hours-status">
          <?php if ($open_brno || $fri_brno): ?>
            <span class="badge badge--success"><?= htmlspecialchars($t['brno_open_today']) ?></span>
          <?php else: ?>
            <span class="badge badge--muted"><?= htmlspecialchars($t['brno_closed_today']) ?></span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Transit -->
      <div class="card booking-info-card">
        <div class="card-heading">
          <i data-lucide="bus" class="card-heading-icon"></i>
          <h3><?= htmlspecialchars($t['brno_transit_label']) ?></h3>
        </div>
        <p class="brno-stop">
          <i data-lucide="map-pin" aria-hidden="true" style="width:14px;height:14px;vertical-align:middle;margin-right:.3rem;color:var(--blue-500)"></i>
          <?= htmlspecialchars($t['brno_stop']) ?>
        </p>
        <p class="brno-transit-line">
          <span class="brno-transit-badge brno-transit-badge--tram"><?= htmlspecialchars($t['brno_tram_label']) ?></span>
          1 &nbsp;·&nbsp; 3 &nbsp;·&nbsp; 11
        </p>
        <p class="brno-transit-line">
          <span class="brno-transit-badge brno-transit-badge--bus"><?= htmlspecialchars($t['brno_bus_label']) ?></span>
          30 &nbsp;·&nbsp; 36 &nbsp;·&nbsp; s88
        </p>
      </div>

      <!-- Parking -->
      <div class="card booking-info-card brno-parking-card">
        <img src="assets/parkplatz.png"
             alt="<?= htmlspecialchars($t['alt_brno_parking']) ?>"
             class="brno-parking-img" loading="lazy">
        <div class="brno-parking-text">
          <i data-lucide="circle-parking" aria-hidden="true" style="color:var(--blue-500);width:18px;height:18px;flex-shrink:0"></i>
          <span><?= htmlspecialchars($t['brno_parking']) ?></span>
        </div>
      </div>

    </div><!-- /sidebar -->

  </div><!-- /booking-grid -->
</div>
