@extends('dashboard.home')
@section('content')
    <div class="row">
        <div class="col-md-12 mt-5">
            <div class="row clearfix">
                <div class="col-xl-3 col-md-6">
                    <div class="card mg-b-30 bg-teal rounded shadow-1">
                        <div class="pd-20 align-items-center card-img">
                            <div class="ft-left">
                                <p class="tx-10 tx-spacing-1 tx-mont tx-medium tx-uppercase mg-b-10 text-white">{{ __('form.employees.employees_count') }}</p>
                                <p class="tx-26 tx-inverse tx-black mg-b-0 lh-1 text-white">{{ $employeesCount }}</p>
                            </div>
                            <div class="ft-right">
                                <i class="fa fa-group tx-80  tx-primary op-5 text-white ft-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card mg-b-30 bg-danger rounded shadow-1">
                        <div class="pd-20 align-items-center card-img">
                            <div class="ft-left">
                                <p class="tx-10 tx-spacing-1 tx-mont tx-medium tx-uppercase mg-b-10 text-white">{{ __('form.accident.accidentrecords') }}</p>
                                @foreach($accidentTypes as $accidentType)
                                    <p class="tx-13 tx-inverse tx-black mg-b-0 lh-1 text-white">{{ $accidentType->hname }}
                                        ({{ $accidentType->accidentRecords_count }})</p>
                                @endforeach
                            </div>
                            <div class="ft-right">
                                <i class="fa fa-ambulance tx-80  tx-primary op-5 text-white ft-right "></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card mg-b-30 bg-primary rounded shadow-1">
                        <div class="pd-20 align-items-center card-img">
                            <div class="ft-left">
                                @if(isset($medicalOrder))

                                    <p class="tx-10 tx-spacing-1 tx-mont tx-medium tx-uppercase mg-b-10 text-white">{{ __('form.medical_orders.medical_order') }}
                                       </p>
                                    <p class="tx-18 tx-inverse tx-black mg-b-0 lh-1 text-white">{{ __('form.total') }}
                                        : {{ $medicalOrder->order_employees_count }}</p>
                                    <span
                                        class="tx-13 tx-poppins tx-gray-600 text-white">{{ __('form.submitted') }} : {{ $medicalOrder->medical_results_count }}</span>
                                @endif
                            </div>
                            <div class="ft-right">
                                <i class="fa fa-hospital-o tx-80  tx-teal op-5 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card mg-b-30 bg-info rounded shadow-1">
                        <div class="pd-20 align-items-center card-img">
                            <div class="ft-left">
                                <p class="tx-10 tx-spacing-1 tx-mont tx-medium tx-uppercase mg-b-10 text-white">{{ __('quiz.quiz') }}</p>
                                <p class="tx-20 tx-inverse tx-black mg-b-0 lh-1 text-white">{{ __('form.total') }} : {{ $exams['all'] }}</p>
                                <span class="tx-13 tx-poppins tx-gray-600 text-white">{{ __('quiz.status_active') }} : {{ $exams['active'] }}</span>
                            </div>
                            <div class="ft-right">
                                <i class="fa fa-calendar-check-o tx-80  tx-teal op-5 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
{{--        <div class=" col-md-12 mt-3 mb-6 p-5 d-flex">--}}
{{--            <div id="chart" style="height: 370px; width: 100%;"></div>--}}
{{--        </div>--}}
        <div class=" col-md-6 mt-3 mb-6 p-3">
            <div id="chartContainer" style="height: 370px; width: 100%;"></div>
        </div>

    </div>
@endsection

@section('script')
    <script type="text/javascript">
        window.onload = function () {
            fetch('/admin/warehouse-category/all')
                .then(response => response.json())
                .then(data => {
                    let dataPoints = data.map(category => {
                        return {y: category.y, name: category.name};
                    });

                    var options = {
                        exportEnabled: true,
                        animationEnabled: true,
                        title: {
                            text: "{{ __('form.warehouse.products') }} ({{ date('d.m.Y') }})"
                        },
                        legend: {
                            horizontalAlign: "left",
                            verticalAlign: "center"
                        },
                        data: [{
                            type: "pie",
                            showInLegend: true,
                            toolTipContent: "<b>{name}</b>: {y} (#percent%)",
                            indexLabel: "{name}",
                            legendText: "{name} ({y})",
                            indexLabelPlacement: "inside",
                            dataPoints: dataPoints
                        }]
                    };
                    $("#chartContainer").CanvasJSChart(options);
                })
                .catch(error => console.error('Error fetching categories:', error));
        }
    </script>
{{--    <script>--}}
{{--        window.onload = function () {--}}
{{--            fetch('/admin/warehouse-category/getByDate')--}}
{{--                .then(response => response.json())--}}
{{--                .then(data => {--}}
{{--                    let items = data.map(category => {--}}
{{--                        return { x: new Date(category.x), y: category.y };--}}
{{--                    });--}}
{{--                    var chart = new CanvasJS.Chart("chart", {--}}
{{--                        animationEnabled: true,--}}
{{--                        title: {--}}
{{--                            text: "Daily High Temperature at Different Beaches"--}}
{{--                        },--}}
{{--                        axisX: {--}}
{{--                            valueFormatString: "DD MMM,YY"--}}
{{--                        },--}}
{{--                        axisY: {--}}
{{--                            title: "Temperature (in °C)",--}}
{{--                            suffix: " °C"--}}
{{--                        },--}}
{{--                        legend: {--}}
{{--                            cursor: "pointer",--}}
{{--                            fontSize: 16,--}}
{{--                            itemclick: toggleDataSeries--}}
{{--                        },--}}
{{--                        toolTip: {--}}
{{--                            shared: true--}}
{{--                        },--}}
{{--                        data: [{--}}
{{--                            name: "API Temperature Data",--}}
{{--                            type: "spline",--}}
{{--                            yValueFormatString: "#0.## °C",--}}
{{--                            showInLegend: true,--}}
{{--                            dataPoints: items--}}
{{--                        }]--}}
{{--                    });--}}

{{--                    chart.render();--}}

{{--                    function toggleDataSeries(e) {--}}
{{--                        if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {--}}
{{--                            e.dataSeries.visible = false;--}}
{{--                        } else {--}}
{{--                            e.dataSeries.visible = true;--}}
{{--                        }--}}
{{--                        chart.render();--}}
{{--                    }--}}
{{--                })--}}
{{--                .catch(error => {--}}
{{--                    console.error('Error fetching data:', error);--}}
{{--                });--}}
{{--        }--}}
{{--    </script>--}}
    {{--    <script src="{{ asset("assets/plugins/chart/jquery-1.11.1.min.js") }}"></script>--}}
    <script src="{{ asset("assets/plugins/chart/jquery.canvasjs.min.js") }}"></script>
@endsection


