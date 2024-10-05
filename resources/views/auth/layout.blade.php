<html lang="zxx"><head>
    <!-- The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keyword" content="">
    <meta name="author" content="">
    <!-- Page Title -->
    <title>Sing In | Adminify - Multipurpose Admin Dashboard Template</title>
    <!-- Main CSS -->
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/simple-line-icons/css/simple-line-icons.css') }}" rel="stylesheet">
    {{--    <link href="{{ asset('assets/plugins/ionicons/css/ionicons.css') }}" rel="stylesheet">--}}
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon">
</head>
<body class="card-img-2  pace-done">
<div class="pace  pace-inactive">
    <div class="pace-progress" data-progress-text="100%" data-progress="99" style="transform: translate3d(100%, 0px, 0px);">
        <div class="pace-progress-inner"></div>
    </div>
    <div class="pace-activity"></div></div>
<!--================================-->
<!-- User Singin Start -->
<!--================================-->
@yield('content')
<!--================================-->
<!-- Footer Script -->
<!--================================-->
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery-ui/jquery-ui.js') }}"></script>
<script src="{{ asset('assets/plugins/popper/popper.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/plugins/pace/pace.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('assets/js/custom.js') }}"></script>
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
</body></html>

