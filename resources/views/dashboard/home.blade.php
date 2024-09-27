<!DOCTYPE html>
<html lang="zxx">
<head>
    <!-- The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keyword" content="">
    <meta name="author" content=""/>
    <!-- Page Title -->
    <title>apiagrar.faceai.uz</title>
    <!-- Main CSS -->
    <link href="{{ asset("assets/plugins/bootstrap/css/bootstrap.min.css") }}" rel="stylesheet"/>
    <link href="{{ asset("assets/plugins/font-awesome/css/font-awesome.min.css") }}" rel="stylesheet"/>
    <link href="{{ asset("assets/plugins/simple-line-icons/css/simple-line-icons.css") }}" rel="stylesheet">
    <link href="{{ asset("assets/plugins/ionicons/css/ionicons.css") }}" rel="stylesheet">
    <link href="{{ asset("assets/plugins/toastr/toastr.min.css") }}" rel="stylesheet">
    {{--    <link href="{{ asset("assets/css/skin-turquoise.css") }}" rel="stylesheet" id="style-colors">--}}
    <link href="{{ asset("assets/css/app.min.css") }}" rel="stylesheet"/>
    <link href="{{ asset("assets/css/style.css") }}" rel="stylesheet"/>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset("assets/images/favicon.ico") }}" type="image/x-icon">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn"t work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    @yield('head')
    <style>
        .button-2x {
            font-size: large;
        }
    </style>
</head>
<body>
<!--================================-->
<!-- Page Container Start -->
<!--================================-->
<div class="page-container">
    <!--================================-->
    <!-- Page Sidebar Start -->
    <!--================================-->
    <div class="page-sidebar">
        <a class="logo-box" href="">
            <span><img src="{{ asset("assets/images/logo-white.png") }}" alt=""></span>
            <i class="ion-aperture" id="fixed-sidebar-toggle-button"></i>
            <i class="ion-ios-close-empty" id="sidebar-toggle-button-close"></i>
        </a>
        @include('layouts.menu')
        <!--================================-->
        <!-- Sidebar Footer Start -->
        <!--================================-->
        <div class="sidebar-footer d-flex justify-content-around">
            <a class="pull-left" href="{{ route('auth.logout') }}" data-toggle="tooltip" data-placement="top"
               data-original-title="{{ __('auth.logOut') }}">
                <i class="icon-power"></i></a>
        </div>
        <!--/ Sidebar Footer End -->
    </div>
    <!--/ Page Sidebar End -->
    <!--================================-->
    <!-- Page Content Start -->
    <!--================================-->
    <div class="page-content ht-100v">
        <!--================================-->
        <!-- Page Header Start -->
        <!--================================-->
        @include('layouts.navbar')
        <!--/ Page Header End -->
        <!--================================-->
        <!-- Page Inner Start -->
        <!--================================-->
        <div class="page-inner
{{--         ht-100v--}}
         ">
            <!--================================-->
            <!-- Main Wrapper Start -->
            <!--================================-->
            <div id="main-wrapper">
                <!--================================-->
                <!-- Breadcrumb Start -->
                <!--================================-->
                {{--                @include('layouts.page_breadcrumb')--}}
                {{--                <!--/ Breadcrumb End -->--}}
                @yield('content')
{{--                @dd(app()->getLocale())--}}
            </div>
            <!--/ Main Wrapper End -->
        </div>
        <!--/ Page Inner End -->
        <!--================================-->
        <!-- Page Footer Start -->
        <!--================================-->
        {{--        <footer class="page-footer bg-gray-100">--}}
        {{--            <div class="pd-y-10 pd-x-25">--}}
        {{--                <span class="tx-italic text-muted">Copyright&copy; 2024</span>--}}
        {{--            </div>--}}
        {{--        </footer>--}}
        <!-- Page Footer End -->
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Container -->
<!--================================-->
<!-- Color switcher Start -->
<!--================================-->
{{--@include('admin.layouts.color_switcher')--}}
<!--/ Color switcher  End  -->
<!--================================-->
<!-- Scroll To Top Start-->
<!--================================-->
<a href="#" data-click="scroll-top" class="btn-scroll-top fade"><i class="fa fa-arrow-up"></i></a>
<!--/ Scroll To Top End -->
<!--================================-->
<!-- Footer Script -->
<!--================================-->
<script src="{{ asset("assets/plugins/jquery/jquery.min.js") }}"></script>
<script src="{{ asset("assets/plugins/jquery-ui/jquery-ui.js") }}"></script>
<script src="{{ asset("assets/plugins/popper/popper.js") }}"></script>
<script src="{{ asset("assets/plugins/bootstrap/js/bootstrap.min.js") }}"></script>
<script src="{{ asset("assets/plugins/pace/pace.min.js") }}"></script>
<script src="{{ asset("assets/plugins/sparkline/sparkline.min.js") }}"></script>
<script src="{{ asset("assets/js/jquery.slimscroll.min.js") }}"></script>
<script src="{{ asset("assets/js/highlight.min.js") }}"></script>
<script src="{{ asset("assets/js/adminify.js") }}"></script>
<script src="{{ asset("assets/plugins/toastr/toastr.min.js") }}"></script>
<script src="{{ asset("assets/js/custom.js") }}"></script>
@yield('script')
@if(session()->has('res'))
    <script>
        $(document).ready(function () {
            setTimeout(function () {
                toastr.options = {
                    positionClass: 'toast-top-right',
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 3500
                };
                toastr.{{ session('res.method') }}("{{ session('res.msg') }}");

            }, 300);

        });
    </script>
@endif

</body>
</html>
