<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="./images/favicon.png">

    <link href="{{ asset('focus/assets/vendor/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/vendor/owl-carousel/css/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/vendor/owl-carousel/css/owl.theme.default.min.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/vendor/jqvmap/css/jqvmap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/my_style.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/icons/simple-line-icons/css/simple-line-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/icons/themify-icons/css/themify-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/icons/material-design-iconic-font/css/materialdesignicons.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('focus/assets/icons/font-awesome-old/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Buttons extension CSS & JS -->
   <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">



</head>

<body>

    <div id="loader-wrapper">
        <div id="loader"></div>
        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>
    </div>

    <div id="main-wrapper">
        @include('layouts.header')
        @include('layouts.sidebar')

        <div class="content-body">
            <div class="container-fluid">

                @yield('content')

            </div>
        </div>

        @include('layouts.footer')
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script src="{{ asset('focus/assets/vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('focus/assets/js/custom.min.js') }}"></script>
    <script src="{{ asset('focus/assets/js/quixnav-init.js') }}"></script>
    <script src="{{ asset('focus/assets/vendor/raphael/raphael.min.js') }}"></script>
    <script src="{{ asset('focus/assets/vendor/morris/morris.min.js') }}"></script>
    <script src="{{ asset('focus/assets/vendor/circle-progress/circle-progress.min.js') }}"></script>
    <script src="{{ asset('focus/assets/vendor/chart.js/Chart.bundle.min.js') }}"></script>
    <script src="{{ asset('focus/assets/vendor/gaugeJS/dist/gauge.min.js') }}"></script>
    <script src="{{ asset('focus/assets/vendor/flot/jquery.flot.js') }}"></script>
    <script src="{{ asset('focus/assets/vendor/flot/jquery.flot.resize.js') }}"></script>
    <script src="{{ asset('focus/assets/vendor/owl-carousel/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('focus/assets/vendor/jqvmap/js/jquery.vmap.min.js') }}"></script>
    <script src="{{ asset('focus/assets/vendor/jqvmap/js/jquery.vmap.usa.js') }}"></script>
    <script src="{{ asset('focus/assets/vendor/jquery.counterup/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('focus/assets/vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('focus/assets/js/plugins-init/datatables.init.js') }}"></script>
    <script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>

    <!-- Buttons extension -->
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- <script src="{{ asset('focus/assets/js/dashboard/dashboard-1.js') }}"></script> --}}
    <script>
        $("#loader-wrapper").show();
        $(window).on('load', () => $("#loader-wrapper").hide());
    </script>

    @stack('scripts')
</body>

</html>
