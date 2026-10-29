/* Mode gelap/terang, tombol lihat kata sandi, dan umpan balik tombol. */
(function () {
    var root = document.documentElement;

    function currentTheme() {
        return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function syncToggles() {
        var dark = currentTheme() === 'dark';
        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            var label = dark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap';
            btn.setAttribute('aria-label', label);
            btn.setAttribute('title', label);
            btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
        });
    }

    function setTheme(theme, save) {
        root.classList.add('theme-anim');
        root.setAttribute('data-theme', theme);
        if (save) {
            try { localStorage.setItem('theme', theme); } catch (e) {}
        }
        syncToggles();
        window.setTimeout(function () { root.classList.remove('theme-anim'); }, 450);
    }

    document.addEventListener('click', function (e) {
        var toggle = e.target.closest('[data-theme-toggle]');
        if (toggle) {
            setTheme(currentTheme() === 'dark' ? 'light' : 'dark', true);
            return;
        }

        var reveal = e.target.closest('[data-reveal]');
        if (reveal) {
            var wrap = reveal.closest('.input-wrap');
            var input = wrap && wrap.querySelector('input');
            if (!input) { return; }

            var show = input.type === 'password';
            var pos = input.selectionStart;
            input.type = show ? 'text' : 'password';
            reveal.setAttribute('aria-pressed', show ? 'true' : 'false');
            reveal.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            input.focus();
            try { input.setSelectionRange(pos, pos); } catch (err) {}
        }
    });

    // Ikuti pengaturan sistem selama pengguna belum memilih sendiri.
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
            var saved = null;
            try { saved = localStorage.getItem('theme'); } catch (err) {}
            if (!saved) { setTheme(e.matches ? 'dark' : 'light', false); }
        });
    }

    // Sinkron antar tab.
    window.addEventListener('storage', function (e) {
        if (e.key === 'theme' && (e.newValue === 'dark' || e.newValue === 'light')) {
            setTheme(e.newValue, false);
        }
    });

    // Animasi memuat pada tombol kirim.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'post') { return; }
        var btn = form.querySelector('button[type="submit"]');
        if (!btn) { return; }
        window.setTimeout(function () {
            btn.classList.add('is-loading');
            btn.disabled = true;
        }, 0);
    });

    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) { return; }
        document.querySelectorAll('.btn.is-loading').forEach(function (b) {
            b.classList.remove('is-loading');
            b.disabled = false;
        });
    });

    // Nama berkas pada kotak unggah.
    document.addEventListener('change', function (e) {
        var input = e.target.closest('[data-file-input]');
        if (!input) { return; }
        var out = document.querySelector(input.getAttribute('data-file-input'));
        if (out) { out.textContent = input.files.length ? input.files[0].name : ''; }
    });

    ['dragover', 'dragleave', 'drop'].forEach(function (type) {
        document.addEventListener(type, function (e) {
            var zone = e.target.closest && e.target.closest('.dropzone');
            if (zone) { zone.classList.toggle('is-over', type === 'dragover'); }
        });
    });

    syncToggles();
})();
