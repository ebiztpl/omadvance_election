<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Login')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="./images/favicon.png">

    <link href="{{ asset('focus/assets/vendor/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/vendor/owl-carousel/css/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/vendor/owl-carousel/css/owl.theme.default.min.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/vendor/jqvmap/css/jqvmap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/css/style.css') }}" rel="stylesheet">

    <style>
        .alert {
            color: black !important;
        }
    </style>
</head>

<body>

    @yield('content')
    <script src="{{ asset('focus/assets/vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('focus/assets/js/custom.min.js') }}"></script>
    <script src="{{ asset('focus/assets/js/quixnav-init.js') }}"></script>

</body>

</html>
