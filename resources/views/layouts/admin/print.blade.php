<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
    <!-- Title -->
    <title></title>
    <!-- Favicon -->

    <link rel="shortcut icon" href="">
    <link rel="icon" type="image/x-icon" href="">
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;display=swap" rel="stylesheet">
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/vendor.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/vendor/icon-set/style.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/custom.css')}}">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/theme.minc619.css?v=1.0')}}">
    @stack('css_or_js')

    <style>
        :root {
            --theameColor: #045cff;
        }
        .scroll-bar {
            max-height: calc(100vh - 100px);
            overflow-y: auto !important;
        }

        ::-webkit-scrollbar-track {
            box-shadow: inset 0 0 1px #cfcfcf;
            /*border-radius: 5px;*/
        }

        ::-webkit-scrollbar {
            width: 3px!important;
            height: 3px!important;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            /*border-radius: 5px;*/
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #003638;
        }

        @media only screen and (max-width: 768px) {
            /* For mobile phones: */
            .map-warper {
                height: 250px;
                padding-bottom: 10px;
            }
        }
        .deco-none {
            color: inherit;
            text-decoration: inherit;
        }
        .qcont {
            text-transform: lowercase;
        }
        .qcont:first-letter {
            text-transform: capitalize;
        }

        /* .navbar-vertical .nav-link {
            color: #ffffff !important;
        }

        .navbar .active > .nav-link, .navbar .nav-link.active, .navbar .nav-link.show, .navbar .show > .nav-link {
            color: #C6FFC1 !important;
        } */



        .navbar-vertical .nav-link {
            color: #E5E5E5;
        }

        .navbar .nav-link:hover {
            color: #C6FFC1;
        }

        .navbar .active > .nav-link, .navbar .nav-link.active, .navbar .nav-link.show, .navbar .show > .nav-link {
            color: #C6FFC1;
        }

        .navbar-vertical .active .nav-indicator-icon, .navbar-vertical .nav-link:hover .nav-indicator-icon, .navbar-vertical .show > .nav-link > .nav-indicator-icon {
            color: #C6FFC1;
        }

        .nav-subtitle {
            display: block;
            color: #6D8C7E;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .03125rem;
        }

        .navbar-vertical .navbar-nav.nav-tabs .active .nav-link, .navbar-vertical .navbar-nav.nav-tabs .active.nav-link {
            border-left-color: #C6FFC1;
        }

        .cursor-pointer{
            cursor: pointer;
        }

        .floating-menu{
            border-radius:100px;
            z-index:999;
            padding-top:10px;
            padding-bottom:10px;
            right:0;
            position:fixed;
            display:inline-block;
            top:50%;
            -webkit-transform:translateY(-50%);
            -ms-transform:translateY(-50%);
            transform:translateY(-50%);
        }
        .main-menu{
            margin:0;
            padding-left:0;
            list-style:none;
        }
        .main-menu li a:hover{
            background:rgba(244,244,244,.3);
        }
        .menu-bg{
            /* background-image:-webkit-linear-gradient(top,#1C5E91 0,#167699 100%);
            background-image:-o-linear-gradient(top,#1C5E91 0,#167699 100%);
            background-image:-webkit-gradient(linear,left top,left bottom,from(#1C5E91),to(#167699));
            background-image:linear-gradient(to bottom,#1C5E91 0,#167699 100%);
            background-repeat:repeat-x; */
            background-color: #334257;
            position:absolute;
            width:100%;height:100%;
            border-radius:50px;z-index:-1;
            top:0;
            left:0;
            -webkit-transition:.1s;
            -o-transition:.1s;
            transition:.1s;
        }
        .ripple{
            position:relative;
            overflow:hidden;
            transform:translate3d(0,0,0);
        }
        .ripple:after{
            content:"";
            display:block;
            position:absolute;
            width:100%;
            height:100%;
            top:0;
            left:0;
            pointer-events:none;
            background-image:radial-gradient(circle,#000 10%,transparent 10.01%);
            background-repeat:no-repeat;
            background-position:50%;
            transform:scale(10,10);
            opacity:0;
            transition:transform .5s,opacity 1s;
        }
        .ripple:active:after{
            transform:scale(0,0);
            opacity:.2;
            transition:0s;
        }
    </style>

    <script
        src="{{asset('public/assets/admin')}}/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js"></script>
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/toastr.css">
</head>

<body class="footer-offset">

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div id="loading" style="display: none;">
                <div style="position: fixed;z-index: 9999; left: 40%;top: 37% ;width: 100%">
                    <img width="200" src="{{asset('public/assets/admin/img/loader.gif')}}">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Builder -->

<!-- End Builder -->

<!-- JS Preview mode only -->

<!-- END ONLY DEV -->

<main id="content" role="main" class="main pointer-event">
    <!-- Content -->
@yield('content')
<!-- End Content -->

<!-- ========== END SECONDARY CONTENTS ========== -->
<script src="{{asset('public/assets/admin')}}/js/custom.js"></script>
<!-- JS Implementing Plugins -->

@stack('script')
<!-- JS Front -->
<script src="{{asset('public/assets/admin')}}/js/vendor.min.js"></script>
<script src="{{asset('public/assets/admin')}}/js/theme.min.js"></script>
<script>
    $(document).on('ready', function () {
        window.print();
        window.onfocus=function(){ window.close();}
    })
</script>
<!-- IE Support -->
<script>
    if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) document.write('<script src="{{asset('public/assets/admin')}}/vendor/babel-polyfill/polyfill.min.js"><\/script>');
</script>
</body>
</html>
