@extends('dashboard.home')
@section('content')
    <div class="pageheader pd-y-25">
        <div class="pd-t-5 pd-b-5">
            <h1 class="pd-0 mg-0 tx-20 text-overflow">Undefined Users</h1>
        </div>
    </div>

    <div class="row mb-4 pd-25 bg-white clearfix">
        <form action="{{ route('dashboard.users') }}" method="GET" class="d-flex">
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
@endsection


