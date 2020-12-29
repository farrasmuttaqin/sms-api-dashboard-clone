@extends('layouts.login', ['title'=> trans('app.new_password')])

@section('content')
    <div class="uk-vertical-align uk-text-center uk-height-1-1" id="login-content">
            <div class="uk-vertical-align-middle uk-panel uk-panel-box">
            <div class="ribbon-wrapper h2 ribbon-red">
                <div class="ribbon-front">
                    <h2>@lang('app.new_password')</h2>
                </div>
                <div class="ribbon-edge-topleft2"></div>
                <div class="ribbon-edge-bottomleft"></div>
            </div>
            <br>
            <form class="uk-form uk-margin-top" method="POST" action="{{ route('auth.password.request') }}">
                @include('components.alert-success')
                @include('components.alert-danger')
                {!! csrf_field() !!}
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="uk-form-row">
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="Email" class="uk-width-1-1 uk-form-small" />
                </div>
                <div class="uk-form-row">
                    <input name="password" type="password" placeholder="Password" class="uk-width-1-1 uk-form-small" />
                </div>
                <div class="uk-form-row">
                    <input name="password_confirmation" type="password" placeholder="Confirm Password" class="uk-width-1-1 uk-form-small" />
                </div>
                <div class="uk-form-row uk-text-left">
                    <button class="uk-width-1-1 uk-button uk-button-primary uk-button-small uk-float-right">Submit New Password</button>
                </div>
            </form>
        </div>
    </div>
@endsection