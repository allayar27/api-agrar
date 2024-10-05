@extends('dashboard.home')

@section('content')
    <div class="d-flex justify-content-center mt-5">
        <div class="col-lg-10 col-xlg-9 col-md-7 ">
            <div class="card">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">

                        <a class="nav-item nav-link  col-6" id="nav-profile-tab" data-toggle="tab" href="#my-profile"
                           role="tab" aria-controls="my-profile" aria-selected="false">{{ __('form.uz') }}</a>
                        <a class="nav-item nav-link col-6 active show" id="nav-contact-tab" data-toggle="tab"
                           href="#my-contact"
                           role="tab"
                           aria-controls="my-contact" aria-selected="true">{{ __('form.ru') }}</a>
                    </div>
                </nav>
                <form class="form-horizontal" action="{{ route('departments.update',[$item->id])}}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="tab-content" id="pills-tabContent">

                        <div class="tab-pane fade" id="my-profile" role="tabpanel"
                             aria-labelledby="nav-profile-tab">
                            <div class="card-body">

                                <div class="form-group">
                                    <label class="col-md-12">{{ __('validation.attributes.name') }}</label>
                                    <div class="col-md-12">
                                        <input type="text" name="name[uz]" class="form-control"
                                               value="{{ $item->name_uz }}"
                                        >
                                        @if($errors->has('name'))
                                            <div class="text-danger">{{ $errors->first('name') }}</div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="tab-pane fade active show" id="my-contact" role="tabpanel"
                             aria-labelledby="nav-contact-tab">
                            <div class="card-body">

                                <div class="form-group">
                                    <label class="col-md-12">{{ __('validation.attributes.name') }}</label>
                                    <div class="col-md-12">
                                        <input type="text" name="name[ru]" class="form-control"
                                               value="{{ $item->name_ru }}">
                                        @if($errors->has('name'))
                                            <div class="text-danger">{{ $errors->first('name') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group  text-center">
                        <a href="{{ route('departments.index') }}"
                           class="btn btn-slack ">{{{ __('form.cancel') }}}</a>
                        <button class="btn btn-info">{{ __('form.save') }}</button>
                    </div>
                </form>
            </div>

        </div>
@endsection
