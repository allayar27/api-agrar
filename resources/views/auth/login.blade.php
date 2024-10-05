@extends('auth.layout')
@section('content')
    <div >
        <div class="mg-y-120">
            <div class="card mx-auto wd-350 text-center pd-25 shadow-3">
                <h4 class="card-title mt-3 text-center">Sing In</h4>
                <p class="text-center">Sing in to your account</p>
                <form method="post" action="{{ route('web.loginPost') }}">
                    @csrf
                    @if($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                    @endif
                    <div class="form-group input-group mb-4 mt-0">
                        <div class="input-group-prepend">
                            <span class="input-group-text pd-x-9 text-muted"> <i class="fa fa-envelope"></i> </span>
                        </div>
                        <input class="form-control" placeholder="Email address" type="text" name="name" value="{{ old('username') }}">
                    </div>
                    @if($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                    @endif
                    <div class="form-group input-group mb-5">
                        <div class="input-group-prepend">
                            <span class="input-group-text text-muted"> <i class="fa fa-lock"></i> </span>
                        </div>
                        <input class="form-control" placeholder="Create password" type="password" name="password">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-info btn-block tx-13 hover-white"> Login </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{{--    <div class="nav-tabs-top">--}}
{{--        <ul class="nav nav-tabs">--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link" data-toggle="tab" href="#navs-top-laptop"><i class="fa fa-laptop"></i></a>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link active show" data-toggle="tab" href="#navs-top-desktop"><i class="fa fa-desktop"></i></a>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link" data-toggle="tab" href="#navs-top-basket"><i class="fa fa-shopping-basket"></i></a>--}}
{{--            </li>--}}
{{--        </ul>--}}
{{--        <div class="tab-content">--}}
{{--            <div class="tab-pane fade" id="navs-top-laptop">--}}
{{--                <div class="card-body">--}}
{{--                    <p>Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher synth. Cosby sweater eu banh mi, Aliquip placeat salvia cillum iphone. Seitan aliquip quis cardigan american apparel, butcher voluptate nisi qui.</p>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="tab-pane fade active show" id="navs-top-desktop">--}}
{{--                <div class="card-body">--}}
{{--                    <p>Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan helvetica. Aliquip placeat salvia cillum iphone. Seitan aliquip quis cardigan american apparel, butcher voluptate nisi qui.</p>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="tab-pane fade" id="navs-top-basket">--}}
{{--                <div class="card-body">--}}
{{--                    <p>Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher synth. Aliquip placeat salvia cillum iphone. Seitan aliquip quis cardigan american apparel, butcher voluptate nisi qui.</p>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
@endsection
