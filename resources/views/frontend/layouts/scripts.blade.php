{{--
    Frontend scripts. Order matters: jQuery first (legacy cart/search helpers),
    then Bootstrap bundle, Swiper/Slick, then the theme script.
--}}
<script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>
<script src="{{ asset('assets/js/frontend.js') }}?v={{ setting('version', '1.0.0') }}"></script>
@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @foreach ($errors->all() as $error)
                window.AtsFrontend?.toast("{{ $error }}", 'error');
            @endforeach
        });
    </script>
@endif
