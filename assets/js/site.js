/* Site-wide client behavior. Runs after DOMContentLoaded. */
document.addEventListener('DOMContentLoaded', function () {
  if (window.lucide) lucide.createIcons();

  /* Scroll-reveal */
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) { e.target.classList.add('revealed'); io.unobserve(e.target); }
    });
  }, { threshold: 0.08 });
  document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });

  /* Back-to-top + scrolled header */
  var btt = document.getElementById('back-to-top');
  var hdr = document.querySelector('.site-header');
  window.addEventListener('scroll', function () {
    if (btt) btt.classList.toggle('visible', window.scrollY > 500);
    if (hdr) hdr.classList.toggle('scrolled', window.scrollY > 10);
  }, { passive: true });
  if (btt) btt.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  /* Hamburger nav */
  var menuBtn  = document.getElementById('mobile-menu-btn');
  var mainNav  = document.getElementById('main-nav');
  var backdrop = document.getElementById('nav-backdrop');

  function openNav() {
    mainNav.classList.add('open');
    if (backdrop) backdrop.classList.add('visible');
    menuBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }
  function closeNav() {
    if (mainNav) mainNav.classList.remove('open');
    if (backdrop) backdrop.classList.remove('visible');
    if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }
  if (menuBtn && mainNav) {
    menuBtn.addEventListener('click', function () {
      mainNav.classList.contains('open') ? closeNav() : openNav();
    });
  }
  if (backdrop) backdrop.addEventListener('click', closeNav);
  if (mainNav) mainNav.querySelectorAll('.nav-link').forEach(function (link) {
    link.addEventListener('click', closeNav);
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeNav(); });

  /* Product image thumbnail switcher */
  var mainPhoto = document.querySelector('.product-photo');
  document.querySelectorAll('.product-img-thumb').forEach(function (btn) {
    btn.addEventListener('mouseenter', function () {
      document.querySelectorAll('.product-img-thumb').forEach(function (b) {
        b.classList.remove('product-img-thumb--active');
      });
      btn.classList.add('product-img-thumb--active');
      if (mainPhoto) mainPhoto.src = btn.dataset.img;
    });
  });

  /* AI try-on color switcher */
  var aiGlasses = document.getElementById('ai-glasses');
  document.querySelectorAll('.ai-color-thumb').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.ai-color-thumb').forEach(function (b) {
        b.classList.remove('ai-color-thumb--active');
      });
      btn.classList.add('ai-color-thumb--active');
      if (aiGlasses) aiGlasses.style.color = btn.dataset.color;
    });
  });

  /* High contrast */
  var contrastBtn = document.getElementById('contrast-toggle');
  if (contrastBtn) {
    var hcOn = document.documentElement.classList.contains('high-contrast');
    contrastBtn.setAttribute('aria-pressed', hcOn ? 'true' : 'false');
    contrastBtn.addEventListener('click', function () {
      var on = document.documentElement.classList.toggle('high-contrast');
      if (typeof localStorage !== 'undefined') localStorage.setItem('highContrast', on ? '1' : '0');
      contrastBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }

  /* Text zoom */
  var zoomSizes = { a: '', aa: '112.5%', aaa: '125%' };
  function applyZoom(level) {
    document.documentElement.style.fontSize = zoomSizes[level] || '';
    document.querySelectorAll('.text-zoom__btn').forEach(function (btn) {
      var active = btn.dataset.zoom === level;
      btn.classList.toggle('text-zoom__btn--active', active);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }
  var savedZoom = (typeof localStorage !== 'undefined' && localStorage.getItem('textZoom')) || 'a';
  applyZoom(savedZoom);
  document.querySelectorAll('.text-zoom__btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var level = btn.dataset.zoom;
      if (typeof localStorage !== 'undefined') localStorage.setItem('textZoom', level);
      applyZoom(level);
    });
  });
});

/* Secret admin shortcut: type "admin" anywhere on the page (not while typing in a field). */
(function () {
  var buf = '', target = 'admin';
  document.addEventListener('keydown', function (e) {
    var tag = (e.target && e.target.tagName) || '';
    if (tag === 'INPUT' || tag === 'TEXTAREA' || (e.target && e.target.isContentEditable)) return;
    if (e.key && e.key.length === 1) buf = (buf + e.key.toLowerCase()).slice(-target.length);
    if (buf === target) { buf = ''; window.location.href = 'admin/'; }
  });
})();
