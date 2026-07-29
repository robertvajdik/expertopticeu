<?php
$hours = [
  [$t['hours_mo_fr'], $t['hours_mo_fr_time']],
  [$t['hours_sat'],   $t['hours_sat_time']],
  [$t['hours_sun'],   $t['hours_closed']],
];
$dow        = (int) date('N'); // 1=Mon…7=Sun
$open_today = $dow >= 1 && $dow <= 5;
$sat_today  = $dow === 6;

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking'])) {
  if (empty($_POST['vorname']))  $errors[] = $t['err_firstname'];
  if (empty($_POST['nachname'])) $errors[] = $t['err_lastname'];
  if (empty($_POST['email']))    $errors[] = $t['err_email'];
  if (empty($_POST['termin']))   $errors[] = $t['err_date'];
  if (!$errors) {
    $booking_done = true;
    $bookings_file = __DIR__ . '/../data/bookings.json';
    $all = file_exists($bookings_file) ? (json_decode(file_get_contents($bookings_file), true) ?: []) : [];
    $all[] = [
      'id'        => uniqid('b', true),
      'timestamp' => date('Y-m-d H:i:s'),
      'vorname'   => trim($_POST['vorname']  ?? ''),
      'nachname'  => trim($_POST['nachname'] ?? ''),
      'email'     => trim($_POST['email']    ?? ''),
      'phone'     => trim($_POST['phone']    ?? ''),
      'concern'   => trim($_POST['anliegen'] ?? ''),
      'termin'    => trim($_POST['termin']   ?? ''),
      'sms'       => !empty($_POST['sms']),
      'lang'      => $lang,
      'status'    => 'new',
    ];
    file_put_contents($bookings_file, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  }
}
?>

<div class="page-wrap">
  <p class="eyebrow"><?= htmlspecialchars($t['book_eyebrow']) ?></p>
  <h1><?= htmlspecialchars($t['book_headline']) ?></h1>
  <p class="lead booking-lead">
    <?= htmlspecialchars($t['book_lead']) ?>
  </p>

  <div class="booking-grid">
    <!-- Form -->
    <div class="card booking-card">
      <?php if ($booking_done): ?>
        <div class="alert alert--success">
          <i data-lucide="check-circle"></i>
          <div>
            <strong><?= htmlspecialchars($t['book_success_title']) ?></strong><br>
            <?= htmlspecialchars($t['book_success_body']) ?>
          </div>
        </div>
      <?php else: ?>
        <?php if ($errors): ?>
          <div class="alert alert--error">
            <i data-lucide="alert-circle"></i>
            <ul>
              <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" class="booking-form" novalidate>
          <input type="hidden" name="booking" value="1">

          <div class="form-row">
            <div class="form-field">
              <label for="vorname"><?= htmlspecialchars($t['form_firstname']) ?></label>
              <input type="text" id="vorname" name="vorname" placeholder="Maria"
                     value="<?= htmlspecialchars($_POST['vorname'] ?? '') ?>" required>
            </div>
            <div class="form-field">
              <label for="nachname"><?= htmlspecialchars($t['form_lastname']) ?></label>
              <input type="text" id="nachname" name="nachname" placeholder="Huber"
                     value="<?= htmlspecialchars($_POST['nachname'] ?? '') ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-field form-field--icon">
              <label for="email"><?= htmlspecialchars($t['form_email']) ?></label>
              <i data-lucide="mail"></i>
              <input type="email" id="email" name="email" placeholder="name@beispiel.at"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-field form-field--icon">
              <label for="telefon"><?= htmlspecialchars($t['form_phone']) ?></label>
              <i data-lucide="phone"></i>
              <input type="tel" id="telefon" name="telefon" placeholder="0699 …"
                     value="<?= htmlspecialchars($_POST['telefon'] ?? '') ?>">
            </div>
          </div>

          <div class="form-row">
            <div class="form-field">
              <label for="anliegen"><?= htmlspecialchars($t['form_concern']) ?></label>
              <select id="anliegen" name="anliegen" class="select">
                <option><?= $t['concern_eye_test'] ?></option>
                <option><?= htmlspecialchars($t['concern_varifocal']) ?></option>
                <option><?= htmlspecialchars($t['concern_contacts']) ?></option>
                <option><?= htmlspecialchars($t['concern_repair']) ?></option>
                <option><?= htmlspecialchars($t['concern_happy']) ?></option>
              </select>
            </div>
            <div class="form-field">
              <label for="termin"><?= htmlspecialchars($t['form_date']) ?></label>
              <input type="date" id="termin" name="termin" min="<?= date('Y-m-d') ?>"
                     value="<?= htmlspecialchars($_POST['termin'] ?? '') ?>" required>
            </div>
          </div>

          <label class="checkbox-label">
            <input type="checkbox" name="sms" value="1"
                   <?= (!isset($_POST['booking']) || isset($_POST['sms'])) ? 'checked' : '' ?>>
            <?= htmlspecialchars($t['form_sms']) ?>
          </label>

          <button type="submit" class="btn btn--primary btn--lg btn--block">
            <?= htmlspecialchars($t['form_submit']) ?>
          </button>
          <p class="form-legal">
            <?= sprintf($t['form_legal'], $lang) ?>
          </p>
        </form>
      <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="booking-sidebar">

      <!-- Thomas Scheibl -->
      <div class="master-card">
        <img src="assets/cxzg-3kkk19yomwek-6rgo8.jpg" alt="<?= htmlspecialchars($t['alt_master_card']) ?>" class="master-card__photo">
        <div class="master-card__body">
          <p class="master-card__name">Thomas Scheibl</p>
          <p class="master-card__title"><?= htmlspecialchars($t['sidebar_master_title']) ?></p>
        </div>
      </div>

      <!-- Address -->
      <div class="card booking-info-card">
        <div class="card-heading">
          <i data-lucide="map-pin" class="card-heading-icon"></i>
          <h3><?= htmlspecialchars($t['sidebar_studio']) ?></h3>
        </div>
        <img src="assets/studio-brno.jpg" alt="<?= htmlspecialchars($t['alt_storefront_ext']) ?>" class="storefront-thumb">
        <address class="studio-address">
          <?= htmlspecialchars($t['sidebar_address_1']) ?><br>
          <?= htmlspecialchars($t['sidebar_address_2']) ?><br>
          <?= htmlspecialchars($t['sidebar_address_3']) ?>
        </address>
        <a href="tel:+420603419882" class="studio-phone">+420 603 419 882</a>
        <a href="mailto:brno@tstoptik.com" class="studio-email">brno@tstoptik.com</a>
        <div class="studio-map">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2600.0!2d16.5332!3d49.2208!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47129459d5fc4e23%3A0x0!2sHlavn%C3%AD+131%2C+624+00+Brno!5e0!3m2!1scs!2scz!4v1"
            width="100%" height="220" style="border:0" allowfullscreen loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="Expert OPTIC — Hlavní 131, 624 00 Brno-Komín"></iframe>
        </div>
      </div>

      <!-- Hours -->
      <div class="card booking-info-card">
        <div class="card-heading">
          <i data-lucide="clock" class="card-heading-icon"></i>
          <h3><?= htmlspecialchars($t['sidebar_hours']) ?></h3>
        </div>
        <?php foreach ($hours as $i => $h): ?>
          <div class="hours-row<?= $i < count($hours)-1 ? ' hours-row--border' : '' ?>">
            <span><?= htmlspecialchars($h[0]) ?></span>
            <span class="<?= $h[1] === $t['hours_closed'] ? 'hours-closed' : 'hours-time' ?>">
              <?= htmlspecialchars($h[1]) ?>
            </span>
          </div>
        <?php endforeach; ?>
        <div class="hours-status">
          <?php if ($open_today): ?>
            <span class="badge badge--success"><?= htmlspecialchars($t['hours_open_today']) ?></span>
          <?php elseif ($sat_today): ?>
            <span class="badge badge--success"><?= htmlspecialchars($t['hours_open_sat']) ?></span>
          <?php else: ?>
            <span class="badge badge--muted"><?= htmlspecialchars($t['hours_closed_today']) ?></span>
          <?php endif; ?>
        </div>
        <!-- Happy Hour -->
        <div class="happy-hour-tip">
          <i data-lucide="star" class="happy-hour-tip__icon"></i>
          <div>
            <strong><?= htmlspecialchars($t['happy_tip_title']) ?></strong><br>
            <?= htmlspecialchars($t['happy_tip_text']) ?>
          </div>
        </div>
      </div>

      <!-- Facebook -->
      <a href="https://www.facebook.com/profile.php?id=100054584974326" target="_blank" rel="noopener" class="facebook-card">
        <svg class="facebook-card__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M24 12.073C24 5.404 18.627 0 12 0S0 5.404 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.514c-1.491 0-1.956.93-1.956 1.886v2.268h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
        </svg>
        <div>
          <span class="facebook-card__cta"><?= htmlspecialchars($t['fb_cta']) ?></span>
          <span class="facebook-card__handle">@EXPTERTOPTICWIEN</span>
        </div>
        <i data-lucide="external-link" class="facebook-card__arrow"></i>
      </a>

    </div>
  </div>
</div>
