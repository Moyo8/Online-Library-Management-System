<script>
(function () {
    // Read the same key that app.js uses
    var pref = localStorage.getItem('olms-theme-preference');
    if (!pref || (pref !== 'system' && pref !== 'light' && pref !== 'dark')) {
        pref = 'system';
    }
    // Resolve effective theme
    var effective = pref;
    if (pref === 'system') {
        effective = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    // Apply the data-theme attribute that the CSS targets
    if (effective === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.documentElement.setAttribute('data-bs-theme', 'dark');
    } else {
        document.documentElement.removeAttribute('data-theme');
        document.documentElement.removeAttribute('data-bs-theme');
    }
    document.documentElement.style.colorScheme = effective;
    // Suppress transitions on initial paint so there's no flash
    document.documentElement.classList.add('no-transition');
})();
</script>