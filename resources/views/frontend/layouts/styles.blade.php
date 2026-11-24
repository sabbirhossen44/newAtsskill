{{--
    Frontend styles. Vendored assets live under public/assets/vendor/ (Bootstrap 5.3,
    jQuery, Slick, Swiper, Font Awesome) — see frontend/README.md. Nothing here is
    loaded from a CDN except Google Fonts, so the theme works offline once vendored.
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/frontend.css') }}?v={{ setting('version', '1.0.0') }}">
<style>
    :root {
        --ats-royal: #0044cc;
        --ats-royal-dark: #003399;
        --ats-blue: #005bfe;
        --ats-green: #1fae0e;
        --ats-gold: #cda11f;
        --ats-pale: #eaf2ff;
    }
</style>
