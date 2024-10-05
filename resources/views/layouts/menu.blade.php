<div class="page-sidebar">
    <a class="logo-box" href="{{ route('dashboard.index') }}">
        <span><img src="{{ asset('assets/images/logo-white.png')}}" alt=""></span>
        <i class="ion-aperture" id="fixed-sidebar-toggle-button"></i>
        <i class="ion-ios-close-empty" id="sidebar-toggle-button-close"></i>
    </a>
    <div class="page-sidebar-inner">
        <div class="page-sidebar-menu">
{{--            <ul class="accordion-menu">--}}
{{--                @can('view_organization')--}}
{{--                    <li @if(request()->routeIs('dashboard.index')) class="active" @endif >--}}
{{--                        <a href="{{ route('dashboard.index') }}"><i class="fa fa-home"></i>--}}
{{--                            <span>{{__('form.dashboard')}}</span></a>--}}
{{--                    </li>--}}
{{--                @endcan--}}
{{--                @can('view_organization')--}}
{{--                    <li @if(request()->routeIs('organizations.*')) class="active" @endif >--}}
{{--                        <a href="{{ route('organizations.index') }}"><i class="fa fa-bank"></i>--}}
{{--                            <span>{{__('form.organizations.organization')}}</span></a>--}}
{{--                    </li>--}}
{{--                @endcan--}}
{{--                @canany(['view_category','view_document'])--}}
{{--                    <li class="@if(request()->routeIs('categories.*','documents.*')) active open @endif">--}}
{{--                        <a href="javascript:void(0);"><i class="fa fa-book"></i>--}}
{{--                            <span>{{__('form.documents.documents')}}</span><i--}}
{{--                                class="accordion-icon fa fa-angle-left"></i></a>--}}
{{--                        <ul class="sub-menu" style="display:block">--}}
{{--                            @can('view_category')--}}
{{--                                <li @if(request()->routeIs('categories.*')) class="active" @endif >--}}
{{--                                    <a href="{{ route('categories.index') }}"><i class="fa fa-bookmark-o"></i>--}}
{{--                                        <span>{{__('form.categories.categories')}}</span></a>--}}
{{--                                </li>--}}
{{--                            @endcan--}}
{{--                            @can('view_document')--}}
{{--                                <li @if(request()->routeIs('documents.*')) class="active" @endif >--}}
{{--                                    <a href="{{ route('documents.index') }}"><i class="fa fa-file"></i>--}}
{{--                                        <span>{{__('form.documents.documents')}}</span></a>--}}
{{--                                </li>--}}
{{--                            @endcan--}}
{{--                        </ul>--}}
{{--                    </li>--}}
{{--                @endcanany--}}
{{--                @canany(['view_topic','view_question','view_exam'])--}}
{{--                    <li class="@if(request()->routeIs('topics.*','questions.*', 'exams.*')) active open @endif">--}}
{{--                        <a href="javascript:void(0);"><i class="fa fa-calendar-check-o"></i>--}}
{{--                            <span>{{__('quiz.quiz')}}</span><i class="accordion-icon fa fa-angle-left"></i></a>--}}
{{--                        <ul class="sub-menu" style="display:block">--}}
{{--                            @can('view_topic')--}}
{{--                                <li @if(request()->routeIs('topics.*', 'questions.*')) class="active" @endif >--}}
{{--                                    <a href="{{ route('topics.index') }}"><i class="fa fa-question-circle"></i>--}}
{{--                                        <span>{{__('quiz.topics.topics')}}</span></a>--}}
{{--                                </li>--}}
{{--                            @endcan--}}
{{--                            @can('view_exam')--}}
{{--                                <li @if(request()->routeIs('exams.*')) class="active" @endif >--}}
{{--                                    <a href="{{ route('exams.index') }}"><i class="fa fa-calendar-check-o"></i>--}}
{{--                                        <span>{{__('quiz.quiz')}}</span></a>--}}
{{--                                </li>--}}
{{--                            @endcan--}}
{{--                        </ul>--}}
{{--                    </li>--}}
{{--                @endcanany--}}
{{--                @canany(['view_medicalstatus','view_medicalorder'])--}}
{{--                    <li class="@if(request()->routeIs('medical.statuses.*','medical.orders.*')) active open @endif">--}}
{{--                        <a href="javascript:void(0);"><i class="fa fa-plus-circle"></i>--}}
{{--                            <span>{{__('form.medical.medical')}}</span><i--}}
{{--                                class="accordion-icon fa fa-angle-left"></i></a>--}}
{{--                        <ul class="sub-menu" style="display:block">--}}
{{--                            @can('view_medicalstatus')--}}
{{--                                <li @if(request()->routeIs('medical.statuses.*')) class="active" @endif >--}}
{{--                                    <a href="{{ route('medical.statuses.index') }}"><i class="fa fa-plus-square"></i>--}}
{{--                                        <span>{{__('form.medical.medical_status')}}</span></a>--}}
{{--                                </li>--}}
{{--                            @endcan--}}
{{--                            @can('view_medicalorder')--}}
{{--                                <li @if(request()->routeIs('medical.orders.*')) class="active" @endif >--}}
{{--                                    <a href="{{ route('medical.orders.index') }}"><i class="fa fa-hospital-o"></i>--}}
{{--                                        <span>{{__('form.medical_orders.medical_order')}}</span></a>--}}
{{--                                </li>--}}
{{--                            @endcan--}}
{{--                        </ul>--}}
{{--                    </li>--}}
{{--                @endcanany--}}
{{--                @canany(['view_warehousecategory','view_warehouse'])--}}
{{--                    <li class="@if(request()->routeIs('warehouse.warehousecategory.*','warehouse.*')) active open @endif">--}}
{{--                        <a href="javascript:void(0);"><i class="fa fa-server"></i>--}}
{{--                            <span>{{__('form.warehouse.warehouse')}}</span><i--}}
{{--                                class="accordion-icon fa fa-angle-left"></i></a>--}}
{{--                        <ul class="sub-menu" style="display:block">--}}
{{--                            @can('view_warehousecategory')--}}
{{--                                <li @if(request()->routeIs('warehouse.warehousecategory.*')) class="active" @endif >--}}
{{--                                    <a href="{{ route('warehouse.warehousecategory.index') }}"><i--}}
{{--                                            class="fa fa-product-hunt"></i>--}}
{{--                                        <span>{{__('form.warehouse.warehousecategory')}}</span></a>--}}
{{--                                </li>--}}
{{--                            @endcan--}}
{{--                            @can('view_warehouse')--}}
{{--                                <li @if(request()->routeIs('warehouse.*')) class="active" @endif >--}}
{{--                                    <a href="{{ route('warehouse.index') }}"><i class="fa fa-server"></i>--}}
{{--                                        <span>{{__('form.warehouse.products')}}</span></a>--}}
{{--                                </li>--}}
{{--                            @endcan--}}
{{--                        </ul>--}}
{{--                    </li>--}}
{{--                @endcanany--}}
{{--                @canany(['view_accidenttype','view_accidentrecord'])--}}
{{--                    <li class="@if(request()->routeIs('accident.accidenttype.*','accident.accidentrecord.*')) active open @endif">--}}
{{--                            <a href="javascript:void(0);"><i class="fa fa-ambulance"></i>--}}
{{--                                <span>{{__('form.accident.accident')}}</span><i--}}
{{--                                    class="accordion-icon fa fa-angle-left"></i></a>--}}
{{--                        <ul class="sub-menu" style="display:block">--}}
{{--                            @can('view_accidenttype')--}}
{{--                                <li @if(request()->routeIs('accident.accidenttype.*')) class="active" @endif >--}}
{{--                                    <a href="{{ route('accident.accidenttype.index') }}"><i class="fa fa-bookmark-o"></i>--}}
{{--                                        <span>{{__('form.accident.accidenttype')}}</span></a>--}}
{{--                                </li>--}}
{{--                            @endcan--}}
{{--                            @can('view_accidentrecord')--}}
{{--                                <li @if(request()->routeIs('accident.accidentrecord.*')) class="active" @endif >--}}
{{--                                    <a href="{{ route('accident.accidentrecord.index') }}"><i--}}
{{--                                            class="fa fa-ambulance"></i>--}}
{{--                                        <span>{{__('form.accident.accidentrecord')}}</span></a>--}}
{{--                                </li>--}}
{{--                            @endcan--}}
{{--                        </ul>--}}
{{--                    </li>--}}
{{--                @endcanany--}}
{{--                @can('view_department')--}}
{{--                    <li @if(request()->routeIs('departments.*')) class="active" @endif >--}}
{{--                        <a href="{{ route('departments.index') }}"><i class="fa fa-bar-chart"></i>--}}
{{--                            <span>{{__('form.departments.departments')}}</span></a>--}}
{{--                    </li>--}}
{{--                @endcan--}}
{{--                @can('view_position')--}}
{{--                    <li @if(request()->routeIs('positions.*')) class="active" @endif>--}}
{{--                        <a href="{{ route('positions.index') }}"><i class="fa fa-object-group"></i>--}}
{{--                            <span>{{__('form.positions.positions')}}</span></a>--}}
{{--                    </li>--}}
{{--                @endcan--}}
{{--                @can('view_branch')--}}
{{--                    <li @if(request()->routeIs('branches.*')) class="active" @endif >--}}
{{--                        <a href="{{ route('branches.index') }}"><i class="fa fa-bar-chart"></i>--}}
{{--                            <span>{{__('form.branches.branches')}}</span></a>--}}
{{--                    </li>--}}
{{--                @endcan--}}
{{--                @can('view_employee')--}}
{{--                    <li @if(request()->routeIs('employees.*')) class="active" @endif >--}}
{{--                        <a href="{{ route('employees.index') }}"><i class="fa fa-users"></i>--}}
{{--                            <span>{{__('form.employees.employees')}}</span></a>--}}
{{--                    </li>--}}
{{--                @endcan--}}
{{--                @canany(['view_role','view_permission','view_user'])--}}
{{--                    <li class="@if(request()->routeIs('roles.*','permissions.*','users.*')) active open @endif">--}}
{{--                        <a href="javascript:void(0);"><i class="fa fa-cogs"></i>--}}
{{--                            <span>{{__('form.settings')}}</span><i class="accordion-icon fa fa-angle-left"></i></a>--}}
{{--                        <ul class="sub-menu" style="display:block">--}}
{{--                            @can('view_user')--}}
{{--                                <li @if(request()->routeIs('users.*')) class="active" @endif ><a--}}
{{--                                        href="{{ route('users.index') }}">{{__('form.users.users')}}</a></li>--}}
{{--                            @endcan--}}
{{--                            @can('view_role')--}}
{{--                                <li @if(request()->routeIs('roles.*')) class="active" @endif ><a--}}
{{--                                        href="{{ route('roles.index') }}">{{__('form.roles.roles')}}</a></li>--}}

{{--                            @endcan--}}
{{--                            @can('view_permission')--}}
{{--                                <li @if(request()->routeIs('permissions.*')) class="active" @endif ><a--}}
{{--                                        href="{{ route(('permissions.index')) }}">{{__('form.permissions.permissions')}}</a>--}}
{{--                                </li>--}}
{{--                            @endcan--}}
{{--                        </ul>--}}
{{--                    </li>--}}
{{--                @endcanany--}}


{{--            </ul>--}}
        </div>
        <!--================================-->
        <!-- Sidebar Information Summary -->
        <!--================================-->

    </div>
    <!--================================-->
    <!-- Sidebar Footer Start -->
    <!--================================-->
    <div class="sidebar-footer">
        <a class="pull-left" href="{{ route('auth.logout') }}" data-toggle="tooltip" data-placement="top"
           data-original-title="{{__('auth.logOut')}}">
            <i class="icon-power"></i></a>
    </div>
    <!--/ Sidebar Footer End -->
</div>
