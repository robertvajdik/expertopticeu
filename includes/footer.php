<?php
$news_msg  = '';
$news_kind = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_signup'])) {
    require_once __DIR__ . '/customer_auth.php';
    require_once __DIR__ . '/recaptcha.php';
    if (!recaptcha_verify($_POST['g-recaptcha-response'] ?? null)) {
        $news_msg  = $t['news_err_captcha'] ?? 'Potvrďte prosím, že nejste robot.';
        $news_kind = 'err';
    } else {
        $r = newsletter_subscribe($_POST['newsletter_email'] ?? '', $lang);
        if (!empty($r['ok'])) { $news_msg = $t['news_ok'] ?? 'Děkujeme! Odběr byl aktivován.'; $news_kind = 'ok'; }
        elseif (in_array('email', $r['errors'] ?? [], true)) { $news_msg = $t['news_err_email'] ?? 'Zadejte platný e-mail.'; $news_kind = 'err'; }
        else { $news_msg = $t['news_err'] ?? 'Přihlášení se nezdařilo.'; $news_kind = 'err'; }
    }
}
?>
<section class="footer-newsletter">
  <div class="footer-newsletter__inner">
    <div class="footer-newsletter__text">
      <h3><?= htmlspecialchars($t['news_title'] ?? 'Newsletter Expert OPTIC') ?></h3>
      <p><?= htmlspecialchars($t['news_lead'] ?? 'Přihlaste se k odběru a získejte přednostní přístup k novinkám a slevám.') ?></p>
    </div>
    <form method="post" class="footer-newsletter__form">
      <input type="email" name="newsletter_email" required maxlength="150"
             placeholder="<?= htmlspecialchars($t['news_ph'] ?? 'Váš e-mail') ?>"
             value="<?= htmlspecialchars($_POST['newsletter_email'] ?? '') ?>">
      <button type="submit" name="newsletter_signup" value="1" class="btn btn--primary">
        <i data-lucide="mail"></i>
        <?= htmlspecialchars($t['news_btn'] ?? 'Odebírat') ?>
      </button>
      <?php if (recaptcha_enabled()): ?>
        <div class="footer-newsletter__captcha"><?= recaptcha_widget() ?></div>
      <?php endif; ?>
    </form>
    <?php if ($news_msg): ?>
      <p class="footer-newsletter__msg footer-newsletter__msg--<?= $news_kind ?>"><?= htmlspecialchars($news_msg) ?></p>
    <?php endif; ?>
  </div>
</section>

<footer class="site-footer">
  <div class="site-footer__inner">
    <div class="site-footer__brand">
      <img src="assets/logo-light.png" alt="expert·optic — all for eyes" height="54">
      <p><?= htmlspecialchars($t['footer_tagline']) ?></p>
      <?php
        $_phone_href = 'tel:' . preg_replace('/[^\d+]/', '', $site_info['phone']);
      ?>
      <address>
        <strong><?= htmlspecialchars($site_info['studio_name']) ?></strong><br>
        <?= htmlspecialchars($site_info['owner_name']) ?><br>
        <span class="footer-hours-small">St – Čt: 12:30 – 18:00 · Pá: 11:30 – 17:00</span><br>
        <?= htmlspecialchars($site_info['street']) ?>, <?= htmlspecialchars($site_info['city_postal']) ?><br>
        <a href="<?= htmlspecialchars($_phone_href) ?>"><?= htmlspecialchars($site_info['phone']) ?></a><br>
        <a href="mailto:<?= htmlspecialchars($site_info['email']) ?>"><?= htmlspecialchars($site_info['email']) ?></a><br>
        <a href="https://www.expertoptic.eu" target="_blank" rel="noopener">www.expertoptic.eu</a>
      </address>

      <a href="https://www.facebook.com/profile.php?id=100054584974326" target="_blank" rel="noopener" class="footer-fb-link">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M24 12.073C24 5.404 18.627 0 12 0S0 5.404 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.514c-1.491 0-1.956.93-1.956 1.886v2.268h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
        </svg>
        <?= htmlspecialchars($t['footer_fb']) ?>
      </a>
    </div>

    <div class="site-footer__col">
      <p class="footer-col-heading"><?= htmlspecialchars($t['footer_col_range']) ?></p>
      <a href="?p=collection&amp;cat=Optische+Brillen&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_optical']) ?></a>
      <a href="?p=collection&amp;cat=Optische+Brillen&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_varifocal']) ?></a>
      <a href="?p=collection&amp;cat=Sonnenbrillen&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_sun']) ?></a>
      <a href="?p=collection&amp;cat=Sportbrillen&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_sport']) ?></a>
      <a href="?p=collection&amp;cat=Alle&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_contacts']) ?></a>
    </div>

    <div class="site-footer__col">
      <p class="footer-col-heading"><?= htmlspecialchars($t['footer_col_service']) ?></p>
      <a href="?p=booking&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_svc_checkpoint']) ?></a>
      <a href="?p=booking&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_svc_booking']) ?></a>
      <a href="?p=booking&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_svc_repair']) ?></a>
      <a href="?p=home&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_svc_voucher']) ?></a>
      <a href="?p=collection&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_svc_unique']) ?></a>
      <a href="?p=agb&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_agb']) ?></a>
      <a href="?p=datenschutz&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_datenschutz']) ?></a>
    </div>

    <div class="site-footer__col">
      <p class="footer-col-heading"><?= htmlspecialchars($t['footer_col_hours']) ?></p>
      <span class="footer-hours"><?= $t['footer_hours_week'] ?></span>
      <span class="footer-hours"><?= $t['footer_hours_sat'] ?></span>
      <span class="footer-hours footer-hours--closed"><?= $t['footer_hours_sun'] ?></span>
      <span class="footer-happy-hour"><?= htmlspecialchars($t['footer_happy']) ?></span>
    </div>
  </div>

  <div class="site-footer__bottom">
    <span><?= htmlspecialchars($t['footer_copyright']) ?></span>
    <span>
      <a href="?p=impressum&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_impressum']) ?></a> ·
      <a href="?p=datenschutz&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_datenschutz']) ?></a> ·
      <a href="?p=agb&amp;lang=<?= $lang ?>"><?= htmlspecialchars($t['footer_agb']) ?></a>
    </span>
  </div>
</footer>
