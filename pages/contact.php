<?php
require_once __DIR__ . '/../includes/mail.php';
require_once __DIR__ . '/../includes/recaptcha.php';

$contact_done = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact'])) {
  $name    = trim($_POST['name']    ?? '');
  $email   = trim($_POST['email']   ?? '');
  $phone   = trim($_POST['phone']   ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');

  if ($name === '')                                     $errors[] = $t['contact_err_name'];
  if ($email === '')                                    $errors[] = $t['contact_err_email'];
  elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors[] = $t['contact_err_email_invalid'];
  if ($message === '')                                  $errors[] = $t['contact_err_message'];

  if (!$errors && !recaptcha_verify($_POST['g-recaptcha-response'] ?? null)) {
    $errors[] = $t['contact_err_captcha'];
  }

  if (!$errors) {
    $to      = $site_info['email'];
    $subj    = ($subject !== '' ? $subject : $t['contact_subject_default']) . ' — ' . $name;
    $ip      = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $sender  = sprintf('%s <%s>', $site_info['studio_name'], $site_info['email']);

    $body_lines = [
      'Nová zpráva z kontaktního formuláře (' . strtoupper($lang) . ')',
      '',
      'Jméno:   ' . $name,
      'E-mail:  ' . $email,
      'Telefon: ' . ($phone !== '' ? $phone : '—'),
      'Předmět: ' . ($subject !== '' ? $subject : '—'),
      '',
      'Zpráva:',
      $message,
      '',
      '---',
      'IP: ' . $ip,
      'UA: ' . $ua,
      'Čas: ' . date('Y-m-d H:i:s'),
    ];

    $ok = _mail_send($to, $subj, implode("\n", $body_lines), $sender);
    if ($ok) {
      $contact_done = true;
    } else {
      $errors[] = $t['contact_err_send'];
    }
  }
}

$hours_rows = [
  [$t['hours_mo_fr'], $t['hours_mo_fr_time']],
  [$t['hours_sat'],   $t['hours_sat_time']],
  [$t['hours_sun'],   $t['hours_closed']],
];
?>

<div class="page-wrap">
  <p class="eyebrow"><?= htmlspecialchars($t['contact_eyebrow']) ?></p>
  <h1><?= htmlspecialchars($t['contact_headline']) ?></h1>
  <p class="lead contact-lead"><?= htmlspecialchars($t['contact_lead']) ?></p>

  <div class="contact-grid">
    <!-- Form -->
    <div class="card contact-card">
      <?php if ($contact_done): ?>
        <div class="alert alert--success">
          <i data-lucide="check-circle"></i>
          <div>
            <strong><?= htmlspecialchars($t['contact_success_title']) ?></strong><br>
            <?= htmlspecialchars($t['contact_success_body']) ?>
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

        <h2 class="contact-card__title"><?= htmlspecialchars($t['contact_form_title']) ?></h2>

        <form method="post" class="contact-form" novalidate>
          <input type="hidden" name="contact" value="1">

          <div class="form-row">
            <div class="form-field">
              <label for="name"><?= htmlspecialchars($t['contact_form_name']) ?></label>
              <input type="text" id="name" name="name"
                     value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-field form-field--icon">
              <label for="c_email"><?= htmlspecialchars($t['form_email']) ?></label>
              <i data-lucide="mail"></i>
              <input type="email" id="c_email" name="email"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-field form-field--icon">
              <label for="c_phone"><?= htmlspecialchars($t['form_phone']) ?></label>
              <i data-lucide="phone"></i>
              <input type="tel" id="c_phone" name="phone"
                     value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label for="c_subject"><?= htmlspecialchars($t['contact_form_subject']) ?></label>
              <input type="text" id="c_subject" name="subject"
                     value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
            </div>
          </div>

          <div class="form-field">
            <label for="c_message"><?= htmlspecialchars($t['contact_form_message']) ?></label>
            <textarea id="c_message" name="message" rows="6" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </div>

          <?php if (recaptcha_enabled()): ?>
            <div class="contact-form__captcha"><?= recaptcha_widget() ?></div>
          <?php endif; ?>

          <button type="submit" class="btn btn--primary btn--lg btn--block">
            <?= htmlspecialchars($t['contact_form_submit']) ?>
          </button>
          <p class="form-legal">
            <?= sprintf($t['contact_form_legal'], $lang) ?>
          </p>
        </form>
      <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="contact-sidebar">

      <!-- Contact details -->
      <div class="card booking-info-card">
        <div class="card-heading">
          <i data-lucide="phone" class="card-heading-icon"></i>
          <h3><?= htmlspecialchars($t['contact_info_title']) ?></h3>
        </div>
        <p class="contact-info__label"><?= htmlspecialchars($t['contact_call_us']) ?></p>
        <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $site_info['phone'])) ?>" class="studio-phone">
          <?= htmlspecialchars($site_info['phone']) ?>
        </a>
        <p class="contact-info__label"><?= htmlspecialchars($t['contact_write_us']) ?></p>
        <a href="mailto:<?= htmlspecialchars($site_info['email']) ?>" class="studio-email">
          <?= htmlspecialchars($site_info['email']) ?>
        </a>
      </div>

      <!-- Studio address -->
      <div class="card booking-info-card">
        <div class="card-heading">
          <i data-lucide="map-pin" class="card-heading-icon"></i>
          <h3><?= htmlspecialchars($t['contact_visit_title']) ?></h3>
        </div>
        <img src="assets/studio-brno.jpg" alt="<?= htmlspecialchars($t['alt_storefront_ext']) ?>" class="storefront-thumb">
        <address class="studio-address">
          <?= htmlspecialchars($site_info['studio_name']) ?><br>
          <?= htmlspecialchars($site_info['street']) ?><br>
          <?= htmlspecialchars($site_info['city_postal']) ?><br>
          <?= htmlspecialchars($t['sidebar_address_3']) ?>
        </address>
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
        <?php foreach ($hours_rows as $i => $h): ?>
          <div class="hours-row<?= $i < count($hours_rows)-1 ? ' hours-row--border' : '' ?>">
            <span><?= htmlspecialchars($h[0]) ?></span>
            <span class="<?= $h[1] === $t['hours_closed'] ? 'hours-closed' : 'hours-time' ?>">
              <?= htmlspecialchars($h[1]) ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</div>
