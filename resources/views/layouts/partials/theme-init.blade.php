{{-- Sets the theme before first paint so there is no flash. Visual only: stored choice, else the system setting. --}}
<script>
    (function () {
        var theme = null;
        try { theme = localStorage.getItem('connectly-theme'); } catch (e) {}
        if (theme !== 'light' && theme !== 'dark') {
            theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.setAttribute('data-bs-theme', theme);
    })();
</script>
