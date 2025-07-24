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
    <link href="{{ asset('focus/assets/icons/simple-line-icons/css/simple-line-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/icons/themify-icons/css/themify-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/icons/material-design-iconic-font/css/materialdesignicons.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('focus/assets/icons/font-awesome-old/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('focus/assets/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">


    <!-- Buttons extension CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">


    <style>
        .form-control {
            border: 1px solid gray;
        }

        label {
            color: rgb(32, 38, 44);
        }

        .error {
            color: red
        }

        #loader-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 99999;
            background-color: #0000008a;
        }

        #loader {
            display: block;
            position: relative;
            left: 50%;
            top: 50%;
            width: 150px;
            height: 150px;
            margin: -75px 0 0 -75px;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #3498db;

            -webkit-animation: spin 2s linear infinite;
            /* Chrome, Opera 15+, Safari 5+ */
            animation: spin 2s linear infinite;
            /* Chrome, Firefox 16+, IE 10+, Opera */
            z-index: 1001;
        }

        #loader:before {
            content: "";
            position: absolute;
            top: 5px;
            left: 5px;
            right: 5px;
            bottom: 5px;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #e74c3c;

            -webkit-animation: spin 3s linear infinite;
            /* Chrome, Opera 15+, Safari 5+ */
            animation: spin 3s linear infinite;
            /* Chrome, Firefox 16+, IE 10+, Opera */
        }

        #loader:after {
            content: "";
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #f9c922;

            -webkit-animation: spin 1.5s linear infinite;
            /* Chrome, Opera 15+, Safari 5+ */
            animation: spin 1.5s linear infinite;
            /* Chrome, Firefox 16+, IE 10+, Opera */
        }

        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
                /* Chrome, Opera 15+, Safari 3.1+ */
                -ms-transform: rotate(0deg);
                /* IE 9 */
                transform: rotate(0deg);
                /* Firefox 16+, IE 10+, Opera */
            }

            100% {
                -webkit-transform: rotate(360deg);
                /* Chrome, Opera 15+, Safari 3.1+ */
                -ms-transform: rotate(360deg);
                /* IE 9 */
                transform: rotate(360deg);
                /* Firefox 16+, IE 10+, Opera */
            }
        }

        @keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
                /* Chrome, Opera 15+, Safari 3.1+ */
                -ms-transform: rotate(0deg);
                /* IE 9 */
                transform: rotate(0deg);
                /* Firefox 16+, IE 10+, Opera */
            }

            100% {
                -webkit-transform: rotate(360deg);
                /* Chrome, Opera 15+, Safari 3.1+ */
                -ms-transform: rotate(360deg);
                /* IE 9 */
                transform: rotate(360deg);
                /* Firefox 16+, IE 10+, Opera */
            }
        }

        #loader-wrapper .loader-section {
            position: fixed;
            top: 0;
            width: 50%;
            height: 100%;
            background: #22222285;
            z-index: 1000;
        }

        #loader-wrapper .loader-section.section-left {
            left: 0;
        }

        #loader-wrapper .loader-section.section-right {
            right: 0;
        }

        /* Loaded styles */
        .loaded #loader-wrapper .loader-section.section-left {
            -webkit-transform: translateX(-100%);
            /* Chrome, Opera 15+, Safari 3.1+ */
            -ms-transform: translateX(-100%);
            /* IE 9 */
            transform: translateX(-100%);
            /* Firefox 16+, IE 10+, Opera */

            -webkit-transition: all 0.7s 0.3s cubic-bezier(0.645, 0.045, 0.355, 1.000);
            /* Android 2.1+, Chrome 1-25, iOS 3.2-6.1, Safari 3.2-6  */
            transition: all 0.7s 0.3s cubic-bezier(0.645, 0.045, 0.355, 1.000);
            /* Chrome 26, Firefox 16+, iOS 7+, IE 10+, Opera, Safari 6.1+  */
        }

        .loaded #loader-wrapper .loader-section.section-right {
            -webkit-transform: translateX(100%);
            /* Chrome, Opera 15+, Safari 3.1+ */
            -ms-transform: translateX(100%);
            /* IE 9 */
            transform: translateX(100%);
            /* Firefox 16+, IE 10+, Opera */

            -webkit-transition: all 0.7s 0.3s cubic-bezier(0.645, 0.045, 0.355, 1.000);
            /* Android 2.1+, Chrome 1-25, iOS 3.2-6.1, Safari 3.2-6  */
            transition: all 0.7s 0.3s cubic-bezier(0.645, 0.045, 0.355, 1.000);
            /* Chrome 26, Firefox 16+, iOS 7+, IE 10+, Opera, Safari 6.1+  */
        }

        .loaded #loader {
            opacity: 0;

            -webkit-transition: all 0.3s ease-out;
            /* Android 2.1+, Chrome 1-25, iOS 3.2-6.1, Safari 3.2-6  */
            transition: all 0.3s ease-out;
            /* Chrome 26, Firefox 16+, iOS 7+, IE 10+, Opera, Safari 6.1+  */

        }

        .loaded #loader-wrapper {
            visibility: hidden;

            -webkit-transform: translateY(-100%);
            /* Chrome, Opera 15+, Safari 3.1+ */
            -ms-transform: translateY(-100%);
            /* IE 9 */
            transform: translateY(-100%);
            /* Firefox 16+, IE 10+, Opera */

            -webkit-transition: all 0.3s 1s ease-out;
            /* Android 2.1+, Chrome 1-25, iOS 3.2-6.1, Safari 3.2-6  */
            transition: all 0.3s 1s ease-out;
            /* Chrome 26, Firefox 16+, iOS 7+, IE 10+, Opera, Safari 6.1+  */
        }

        .member-link {
            color: blue;
            text-decoration: none;
        }

        .member-link:hover {
            color: darkblue;
        }

        .fieldset-bordered {
            border: 2px solid #ccc;
            padding: 15px;
            margin-top: 20px;
            border-radius: 10px;
            background-color: #f9f9f9;
        }

        .fieldset-bordered legend {
            font-weight: bold;
            padding: 0 10px;
            width: auto;
        }

        .icon-arrow-right {
            font-size: 11px !important;
        }


        @media (min-width: 601px) {
            .nav-header {
                height: 50px;
                width: 205px;
            }

            .quixnav {
                width: 205px;
            }

            .header {
                height: 50px;
            }
            
            .quixnav {
                top: 3rem;
            }

            [data-header-position="fixed"] .content-body {
                padding-top: 2.5rem;
            }

            .header .header-content {
                padding-left: 0.3125rem;
            }

            .content-body {
                margin-left: 11.1875rem;
            }

            .content-body .container-fluid {
                padding-right: 0px;
            }
        }
    </style>

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
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>

    {{-- <script src="{{ asset('focus/assets/js/dashboard/dashboard-1.js') }}"></script> --}}
    <script>
        $("#loader-wrapper").show();
        $(window).on('load', () => $("#loader-wrapper").hide());
    </script>

    @stack('scripts')
</body>

</html>
