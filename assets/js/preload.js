/* Applies saved user preferences before first paint to avoid flash. */
(function () {
  var sizes = { a: '', aa: '112.5%', aaa: '125%' };
  var ls = typeof localStorage !== 'undefined' ? localStorage : null;
  var savedZoom = (ls && ls.getItem('textZoom')) || 'a';
  if (sizes[savedZoom]) document.documentElement.style.fontSize = sizes[savedZoom];
  if (ls && ls.getItem('highContrast') === '1') {
    document.documentElement.classList.add('high-contrast');
  }
})();
