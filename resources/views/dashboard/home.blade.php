<!DOCTYPE html>
<html lang="zxx">

<!-- The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="keyword" content="">
<meta name="author" content="" />
<!-- Page Title -->
<title>apiagrar.faceai.uz</title>
<!-- Main CSS -->
<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/simple-line-icons/css/simple-line-icons.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/ionicons/css/ionicons.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/toastr/toastr.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/datepicker/css/datepicker.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/datepicker/css/datepicker.min.css') }}" rel="stylesheet">
{{--    <link href="{{ asset("assets/css/skin-turquoise.css") }}" rel="stylesheet" id="style-colors"> --}}
<link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" />

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Favicon -->
<link rel="icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon">
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

    .pagination {
        display: flex;
        list-style: none;
        padding-left: 0;
        border-radius: 0.25rem;
    }

    .page-item {
        margin: 0 0.25rem;
    }

    .page-item .page-link {
        padding: 0.5rem 0.75rem;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        color: #007bff;
        text-decoration: none;
    }

    .page-item .page-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
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
                <span><img src="{{ asset('assets/images/logo-white.png') }}" alt=""></span>
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
{{--         ht-100v --}}
">
                <!--================================-->
                <!-- Main Wrapper Start -->
                <!--================================-->
                <div id="main-wrapper">
                    <!--================================-->
                    <!-- Breadcrumb Start -->
                    <!--================================-->
                    {{--                @include('layouts.page_breadcrumb') --}}
                    {{--                <!--/ Breadcrumb End --> --}}
                    @yield('content')

                    <div class="pageheader pd-y-25">
                        <div class="pd-t-5 pd-b-5">
                            <h1 class="pd-0 mg-0 tx-20 text-overflow">Undefined Users</h1>
                        </div>
                    </div>

                    <div class="row mb-4 pd-25 bg-white clearfix">
                        <form action="{{ route('dashboard.index') }}" method="GET" class="d-flex">
                            <div class="input-group w-25 ml-3">
                                <input id="datePicker1" type="date" name="date" class="form-control"
                                    value="{{ old('date', $selectedDate) }}" onchange="this.form.submit()">
                            </div>
                        </form>
                        <div class="row-clearfix">
                            <div class="col-md-12 col-lg-12">
                                <div class="card mb-4 shadow-1">
                                    <div class="card-header">
                                        <h4 class="card-header-title">
                                            Action Table
                                        </h4>
                                        <div class="card-header-btn">
                                            <a href="javascript:void(0)" data-toggle="collapse" class="btn btn-info"
                                                data-target="#collapse7" aria-expanded="true"><i
                                                    class="ion-ios-arrow-down"></i></a>
                                            <a href="javascript:void(0)" data-toggle="refresh"
                                                class="btn btn-warning"><i class="ion-android-refresh"></i></a>
                                            <a href="javascript:void(0)" data-toggle="expand" class="btn btn-success"><i
                                                    class="ion-android-expand"></i></a>
                                            <a href="javascript:void(0)" data-toggle="remove" class="btn btn-danger"><i
                                                    class="ion-ios-trash-outline"></i></a>
                                        </div>
                                    </div>
                                    <div class="card-body collapse show" id="collapse7">
                                        <table class="table table-separated">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Full Name</th>
                                                    <th>Hemis ID</th>
                                                    <th>Person Group</th>
                                                    <th>Date</th>
                                                    <th>Device Name</th>
                                                    <th>Device ID</th>
                                                    <th class="text-center w-100px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($users as $user)
                                                    <tr>
                                                        <th scope="row">1</th>
                                                        <td>{{ $user->full_name }}</td>
                                                        <td>{{ $user->hemis_id }}</td>
                                                        <td>{{ $user->PersonGroup }}</td>
                                                        <td>{{ $user->date_time }}</td>
                                                        <td>{{ $user->device_name }}</td>
                                                        <td>{{ $user->device_id }}</td>
                                                        <td class="text-right table-actions">
                                                            <a class="table-action tx-gray-500 mg-r-10"
                                                                href="javascript:void(0)"><i
                                                                    class="fa fa-pencil"></i></a>
                                                            <a class="table-action tx-gray-500 mg-r-10"
                                                                href="javascript:void(0)"><i
                                                                    class="fa fa-trash"></i></a>
                                                            <span class="dropdown-toggle tx-gray-500"
                                                                data-toggle="dropdown" aria-expanded="false"></span>
                                                            <div class="dropdown-menu dropdown-menu-right"
                                                                x-placement="bottom-end"
                                                                style="position: absolute; transform: translate3d(1212px, 137px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                                <a class="dropdown-item" href="javascript:void(0)"><i
                                                                        class="fa fa-book"></i> Details</a>
                                                                <a class="dropdown-item" href="javascript:void(0)"><i
                                                                        class="fa fa-link"></i> Add file</a>
                                                                <a class="dropdown-item" href="javascript:void(0)"><i
                                                                        class="fa fa-bar-chart"></i>
                                                                    Performance</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" style="text-align: center"
                                                            class="px-6 py-4 whitespace-no-wrap text-sm leading">
                                                            {{ __('Ничего не найдено!') }}
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                        <div class="card-footer d-flex justify-content-end">
                                            {{ $users->links('pagination::bootstrap-4') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{--                @dd(app()->getLocale()) --}}
                </div>
                <!--/ Main Wrapper End -->
            </div>
            <!--/ Page Inner End -->
            <!--================================-->
            <!-- Page Footer Start -->
            <!--================================-->
            {{--        <footer class="page-footer bg-gray-100"> --}}
            {{--            <div class="pd-y-10 pd-x-25"> --}}
            {{--                <span class="tx-italic text-muted">Copyright&copy; 2024</span> --}}
            {{--            </div> --}}
            {{--        </footer> --}}
            <!-- Page Footer End -->
        </div>
        <!-- /Page Content -->
    </div>
    <!-- /Page Container -->
    <!--================================-->
    <!-- Color switcher Start -->
    <!--================================-->
    {{-- @include('admin.layouts.color_switcher') --}}
    <!--/ Color switcher  End  -->
    <!--================================-->
    <!-- Scroll To Top Start-->
    <!--================================-->
    <a href="#" data-click="scroll-top" class="btn-scroll-top fade"><i class="fa fa-arrow-up"></i></a>
    <!--/ Scroll To Top End -->
    <!--================================-->
    <!-- Footer Script -->
    <!--================================-->
    <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-ui/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/plugins/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pace/pace.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sparkline/sparkline.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset('assets/js/highlight.min.js') }}"></script>
    <script src="{{ asset('assets/js/adminify.js') }}"></script>
    <script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    @yield('script')
    <script src="{{ asset('assets/plugins/datepicker/datepicker.js') }}"></script>
    <script src="{{ asset('assets/plugins/datepicker/datepicker.min.js') }}"></script>
    {{-- <script>
            $('#datePicker1').datepicker({
                format: 'mm/dd/yyyy',
                startDate: '-3d'
            });
        </script> --}}
    @if (session()->has('res'))
        <script>
            $(document).ready(function() {
                setTimeout(function() {
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
+