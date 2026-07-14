// Theme toggle: flips data-theme on <html> and persists it under 'ng-theme'.
// The initial theme is set by the inline script in layout.php's <head>.
(function () {
  var button = document.getElementById('theme-toggle');
  if (!button) return;

  button.addEventListener('click', function () {
    var root = document.documentElement;
    var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    try {
      localStorage.setItem('ng-theme', next);
    } catch (e) {
      // localStorage unavailable (e.g. blocked) — theme still toggles for this page.
    }
  });
})();
