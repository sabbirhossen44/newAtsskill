/* ATS Skill — theme-one frontend JS. Vanilla + jQuery where the old theme
   relied on it. Every init is guarded so a missing vendor lib never breaks
   the page. */
(function () {
    'use strict';

    var AtsFrontend = {
        toast: function (message, type) {
            var el = document.createElement('div');
            el.className = 'alert alert-' + (type === 'error' ? 'danger' : 'success') +
                ' alert-dismissible fade show position-fixed bottom-0 end-0 m-3';
            el.style.zIndex = 1080;
            el.setAttribute('role', 'alert');
            el.textContent = message;
            var btn = document.createElement('button');
            btn.className = 'btn-close';
            btn.setAttribute('data-bs-dismiss', 'alert');
            el.appendChild(btn);
            document.body.appendChild(el);
            setTimeout(function () { el.remove(); }, 6000);
        }
    };
    window.AtsFrontend = AtsFrontend;

    document.addEventListener('DOMContentLoaded', function () {
        // Scroll-to-top button
        var topBtn = document.querySelector('.ats-scroll-top');
        if (topBtn) {
            window.addEventListener('scroll', function () {
                topBtn.classList.toggle('show', window.scrollY > 300);
            }, { passive: true });
            topBtn.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // Swiper carousels (categories / instructors / testimonials)
        if (window.Swiper) {
            if (document.querySelector('.categories-active')) {
                new Swiper('.categories-active', {
                    slidesPerView: 2, spaceBetween: 16,
                    navigation: { nextEl: '.categories-button-next', prevEl: '.categories-button-prev' },
                    breakpoints: { 576: { slidesPerView: 3 }, 768: { slidesPerView: 4 }, 992: { slidesPerView: 5 }, 1200: { slidesPerView: 6 } }
                });
            }
            if (document.querySelector('.instructor-active-three')) {
                new Swiper('.instructor-active-three', {
                    slidesPerView: 1, spaceBetween: 20,
                    breakpoints: { 576: { slidesPerView: 2 }, 992: { slidesPerView: 3 }, 1200: { slidesPerView: 4 } }
                });
            }
            if (document.querySelector('.testimonial-swiper-active')) {
                new Swiper('.testimonial-swiper-active', {
                    slidesPerView: 1, spaceBetween: 20,
                    navigation: { nextEl: '.testimonial-button-next', prevEl: '.testimonial-button-prev' },
                    breakpoints: { 768: { slidesPerView: 2 }, 1200: { slidesPerView: 3 } }
                });
            }
        }

        // Add to cart (delegated; the cart controller owns the server side).
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.add-to-cart');
            if (!btn) return;
            e.preventDefault();
            var id = btn.getAttribute('data-id');
            var token = document.querySelector('meta[name="csrf-token"]');
            fetch('/add-to-cart/' + id, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).then(function (r) { return r.json().catch(function () { return {}; }); })
              .then(function (data) {
                  var count = document.querySelector('.ats-cart-count');
                  if (count && data.cart_count !== undefined) count.textContent = data.cart_count;
                  AtsFrontend.toast(data.message || 'Added to cart', data.status === 'error' ? 'error' : 'success');
                  if (btn.getAttribute('data-redirect') === 'true' && data.redirect) window.location.href = data.redirect;
              })
              .catch(function () { AtsFrontend.toast('Could not add to cart. Please try again.', 'error'); });
        });
    });
})();
