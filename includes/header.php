<?php
/* Build a URL that preserves ?p= and other current params, swapping only lang */
$current_p   = htmlspecialchars($_GET['p']   ?? 'home');
$current_cat = isset($_GET['cat']) ? '&amp;cat=' . htmlspecialchars(urlencode($_GET['cat'])) : '';
$current_id  = isset($_GET['id'])  ? '&amp;id='  . htmlspecialchars($_GET['id'])              : '';
$current_tab = isset($_GET['tab']) ? '&amp;tab=' . htmlspecialchars($_GET['tab'])              : '';
$base_qs     = '?p=' . $current_p . $current_cat . $current_id . $current_tab;
?>
<a href="#main-content" class="skip-link">Zum Inhalt springen</a>
<div class="nav-backdrop" id="nav-backdrop"></div>
<header class="site-header">
  <div class="site-header__inner">
    <a href="?p=home&amp;lang=<?= $lang ?>" class="site-header__logo" aria-label="<?= htmlspecialchars($t['aria_logo']) ?>">
      <img src="assets/logo.png" alt="expert·optic — all for eyes" height="48">
    </a>

    <nav class="site-header__nav" id="main-nav" aria-label="<?= htmlspecialchars($t['aria_main_nav']) ?>">
      <a href="?p=collection&amp;cat=Alle<?= lang_qs($lang) ?>"          class="nav-link<?= $page==='collection'?' nav-link--active':'' ?>"><?= htmlspecialchars($t['nav_glasses']) ?></a>
      <a href="?p=collection&amp;cat=Sonnenbrillen<?= lang_qs($lang) ?>" class="nav-link"><?= htmlspecialchars($t['nav_sunglasses']) ?></a>
      <a href="?p=sport-glasses<?= lang_qs($lang) ?>" class="nav-link<?= $page==='sport-glasses'?' nav-link--active':'' ?>"><?= htmlspecialchars($t['nav_sport']) ?></a>
      <a href="?p=contact-lenses<?= lang_qs($lang) ?>" class="nav-link<?= $page==='contact-lenses'?' nav-link--active':'' ?>"><?= htmlspecialchars($t['nav_contactlenses']) ?></a>
      <a href="?p=home#marken<?= lang_qs($lang) ?>"                      class="nav-link"><?= htmlspecialchars($t['nav_brands']) ?></a>
      <a href="?p=booking<?= lang_qs($lang) ?>"                          class="nav-link<?= $page==='booking'?' nav-link--active':'' ?>"><?= htmlspecialchars($t['nav_about']) ?></a>
      <a href="?p=contact<?= lang_qs($lang) ?>"                          class="nav-link<?= $page==='contact'?' nav-link--active':'' ?>"><?= htmlspecialchars($t['nav_contact'] ?? 'Kontakt') ?></a>

      <!-- Accessibility controls — shown only inside mobile nav (hidden on desktop via CSS) -->
      <div class="nav-utils">
        <div class="text-zoom" role="group" aria-label="Schriftgröße">
          <i data-lucide="eye" class="text-zoom__icon" aria-hidden="true"></i>
          <button class="text-zoom__btn" data-zoom="a"   aria-label="Normale Schrift"  aria-pressed="true">A</button>
          <button class="text-zoom__btn" data-zoom="aa"  aria-label="Mittlere Schrift" aria-pressed="false">AA</button>
          <button class="text-zoom__btn" data-zoom="aaa" aria-label="Große Schrift"    aria-pressed="false">AAA</button>
        </div>
        <div class="lang-switch" role="navigation" aria-label="Language / Sprache / Jazyk">
          <a href="<?= $base_qs ?>&amp;lang=at" class="lang-switch__btn<?= $lang==='at'?' lang-switch__btn--active':'' ?>">AT</a>
          <a href="<?= $base_qs ?>&amp;lang=en" class="lang-switch__btn<?= $lang==='en'?' lang-switch__btn--active':'' ?>">EN</a>
          <a href="<?= $base_qs ?>&amp;lang=cz" class="lang-switch__btn<?= $lang==='cz'?' lang-switch__btn--active':'' ?>">CZ</a>
        </div>
      </div>
    </nav>

    <div class="site-header__actions">
      <button class="icon-btn" aria-label="<?= htmlspecialchars($t['aria_search']) ?>" onclick="document.getElementById('search-bar').classList.toggle('visible')">
        <i data-lucide="search"></i>
      </button>

      <!-- High contrast toggle -->
      <button class="icon-btn contrast-btn" id="contrast-toggle"
              aria-label="Hoher Kontrast ein/aus" aria-pressed="false">
        <i data-lucide="contrast"></i>
      </button>

      <!-- Text zoom -->
      <div class="text-zoom" role="group" aria-label="Schriftgröße">
        <i data-lucide="eye" class="text-zoom__icon" aria-hidden="true"></i>
        <button class="text-zoom__btn" data-zoom="a"   aria-label="Normale Schrift"  aria-pressed="true">A</button>
        <button class="text-zoom__btn" data-zoom="aa"  aria-label="Mittlere Schrift" aria-pressed="false">AA</button>
        <button class="text-zoom__btn" data-zoom="aaa" aria-label="Große Schrift"    aria-pressed="false">AAA</button>
      </div>

      <!-- Language switcher -->
      <div class="lang-switch" role="navigation" aria-label="Language / Sprache / Jazyk">
        <a href="<?= $base_qs ?>&amp;lang=at"
           class="lang-switch__btn<?= $lang === 'at' ? ' lang-switch__btn--active' : '' ?>">AT</a>
        <a href="<?= $base_qs ?>&amp;lang=en"
           class="lang-switch__btn<?= $lang === 'en' ? ' lang-switch__btn--active' : '' ?>">EN</a>
        <a href="<?= $base_qs ?>&amp;lang=cz"
           class="lang-switch__btn<?= $lang === 'cz' ? ' lang-switch__btn--active' : '' ?>">CZ</a>
      </div>

      <?php if (!empty($customer)): ?>
        <a href="?p=account&amp;lang=<?= $lang ?>" class="icon-btn icon-btn--logged-in"
           aria-label="<?= htmlspecialchars($t['aria_account'] ?? 'Můj účet') ?>"
           title="<?= htmlspecialchars($customer['name']) ?>">
          <i data-lucide="circle-user-round"></i>
        </a>
      <?php else: ?>
        <a href="?p=login&amp;lang=<?= $lang ?>" class="icon-btn"
           aria-label="<?= htmlspecialchars($t['auth_login_btn'] ?? 'Přihlásit') ?>"
           title="<?= htmlspecialchars($t['auth_login_btn'] ?? 'Přihlásit') ?>">
          <i data-lucide="log-in"></i>
        </a>
      <?php endif; ?>

      <a href="?p=cart&amp;lang=<?= $lang ?>" class="icon-btn cart-btn" aria-label="<?= htmlspecialchars($t['aria_cart']) ?>">
        <i data-lucide="shopping-bag"></i>
        <?php if ($cart > 0): ?>
          <span class="cart-badge"><?= $cart ?></span>
        <?php endif; ?>
      </a>
      <button class="icon-btn mobile-menu-btn" id="mobile-menu-btn"
              aria-label="<?= htmlspecialchars($t['aria_menu']) ?>"
              aria-expanded="false" aria-controls="main-nav">
        <span class="hamburger" aria-hidden="true">
          <span></span><span></span><span></span>
        </span>
      </button>
    </div>
  </div>

  <div id="search-bar" class="search-bar" role="search">
    <div class="search-bar__inner">
      <i data-lucide="search" style="color:var(--ink-500)"></i>
      <input type="search" placeholder="<?= htmlspecialchars($t['search_placeholder']) ?>" autofocus
             onkeydown="if(event.key==='Escape')this.closest('.search-bar').classList.remove('visible')">
      <button onclick="this.closest('.search-bar').classList.remove('visible')" aria-label="<?= htmlspecialchars($t['aria_close']) ?>">
        <i data-lucide="x"></i>
      </button>
    </div>
  </div>
</header>
