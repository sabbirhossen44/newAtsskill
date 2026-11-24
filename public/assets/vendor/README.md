# Vendored assets — loaded via CDN in layouts

Do NOT download large vendor bundles into this repo. The layouts load these
from CDN, so nothing here is required for the app to run:

- Bootstrap 5.3 — `bootstrap/css/bootstrap.min.css`, `bootstrap/js/bootstrap.bundle.min.js`
  (CDN: https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css and
  .../bootstrap.bundle.min.js)
- jQuery — `jquery/jquery.min.js` (only where legacy plugins need it; admin + new
  frontend code is vanilla JS / Bootstrap 5)
- Swiper — `swiper/swiper-bundle.min.css/.js` (homepage sliders)
- Slick — `slick/slick.css/.js` (legacy sliders, if re-enabled)
- Font Awesome 6 — `fontawesome/css/all.min.css`
  (CDN: https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css)

If a deployment needs offline vendoring, download the files above into the
matching subfolders (`bootstrap/`, `jquery/`, `swiper/`, `slick/`,
`fontawesome/`) and update `admin.partials.styles` /
`frontend.layouts.styles` to point at them. All images fall back to
`public/images/placeholder.svg` when an upload is missing.
