# Vendored assets — loaded locally from here

The frontend layouts (`resources/views/frontend/layouts/styles.blade.php`
and `scripts.blade.php`) load these files from LOCAL paths via
`asset('assets/vendor/...')` — NOT from a CDN. These files must exist in
the repo, or the site loads with no style:

- Bootstrap 5.3.2 — `bootstrap/css/bootstrap.min.css`, `bootstrap/js/bootstrap.bundle.min.js`
  (copied from the old site's frontend CSS; bundle JS downloaded from
  https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js)
- jQuery 3.7.1 — `jquery/jquery.min.js`
  (https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js — do NOT use
  the old site's jQuery 1.11.0, it is too old)
- Swiper 11.0.2 — `swiper/swiper-bundle.min.css`, `swiper/swiper-bundle.min.js`
  (copied from the old site's frontend CSS/JS)
- Slick 1.8.1 — `slick/slick.css` (copied from the old site), `slick/slick.min.js`
  (https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js)
- Font Awesome Free 5.15.4 — `fontawesome/css/all.min.css` plus the whole
  `fontawesome/webfonts/` folder (copied from the old site's backend).
  The CSS references `../webfonts/*.woff2`, so the fonts folder is required —
  copying only the CSS leaves icons broken. Do NOT upgrade to FA 6; the old
  theme uses FA 5 classes.

All images fall back to `public/images/placeholder.svg` when an upload is missing.
