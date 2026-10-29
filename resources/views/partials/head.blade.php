<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light dark">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>

{{-- Pasang tema sebelum halaman digambar agar tidak berkedip --}}
<script>
    (function () {
        try {
            var t = localStorage.getItem('theme');

            if (t !== 'dark' && t !== 'light') {
                t = window.matchMedia('(prefers-color-scheme: dark)').matches
                    ? 'dark'
                    : 'light';
            }

            document.documentElement.setAttribute('data-theme', t);
        } catch (e) {}
    })();
</script>

{{-- CSS utama --}}
<link rel="stylesheet" href="{{ asset('css/theme.css') }}">

{{-- Tema hijau-putih --}}
<link rel="stylesheet" href="{{ asset('css/green.css') }}">