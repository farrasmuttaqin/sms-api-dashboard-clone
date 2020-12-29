@extends('layouts.login', ['title'=> trans('app.forgot_password')])

@section('content')
    <div class="uk-vertical-align uk-text-center uk-height-1-1" id="login-content">
        <div class="uk-vertical-align-middle uk-panel uk-panel-box">
            <div class="ribbon-wrapper h2 ribbon-red">
                <div class="ribbon-front">
                    <h2>@lang('app.forgot_password')</h2>
                </div>
                <div class="ribbon-edge-topleft2"></div>
                <div class="ribbon-edge-bottomleft"></div>
            </div>
            <br>
            <form class="uk-form uk-margin-top" method="POST" action="{{ route('auth.password.email') }}">
                @include('components.alert-success')
                @include('components.alert-danger')
                {!! csrf_field() !!}
                <div class="uk-form-row">
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="Email" class="uk-width-1-1 uk-form-small" />
                </div>
                <div class="uk-form-row">
                    <div class="uk-inline-middle uk-width-6-10 uk-float-left">
                        <input name="captcha" type="text" value="{{ old('password') }}" placeholder="Captcha" class="uk-width-1-1 uk-form-small" />
                    </div>
                    <div class="uk-inline-middle uk-width-1-3 uk-float-right">
                        <a href="{{route('captcha.refresh')}}" id="refresh-captcha">
                            <img class="captcha" src="{{ captcha_src() }}" />
                        </a>
                    </div>
                </div>
                <div class="uk-form-row uk-text-left">
                    <a class="uk-width-1-3 uk-link uk-link-muted uk-text-small" href="{{route('auth.login')}}">Back to login ?</a>
                    <button class="uk-width-1-3 uk-button uk-button-primary uk-button-small uk-float-right">Submit</button>
                </div>
            </form>
        </div>
    </div>
@endsection
