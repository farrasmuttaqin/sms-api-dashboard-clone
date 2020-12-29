@extends('layouts.login',['title' => trans('app.user_login')])

@section('content')
    <div class="uk-vertical-align uk-text-center uk-height-1-1" id="login-content">
        <div class="uk-vertical-align-middle uk-panel uk-panel-box ">
            <div class="ribbon-wrapper h2 ribbon-red">
                <div class="ribbon-front">
                    <h2>@lang('app.user_login')</h2>
                </div>
                <div class="ribbon-edge-topleft2"></div>
                <div class="ribbon-edge-bottomleft"></div>
            </div>
            <br>
            <form class="uk-form uk-margin-top" method="POST" action="{{ route('auth.login') }}">
                @include('components.alert-danger')
                @include('components.alert-success',['status'=> session('status')])
                {!! csrf_field() !!}
                <input type="hidden" name="timezone" />
                <div class="uk-form-row">
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="Email" class="uk-width-1-1 uk-form-small" required="required" />
                </div>

                <div class="uk-form-row">
                    <input name="password" type="password" value="{{ old('password') }}" placeholder="Password" class="uk-width-1-1 uk-form-small" required="required" />
                </div>
                <div class="uk-form-row">
                    <div class="uk-inline-middle uk-width-6-10 uk-float-left">
                        <input name="captcha" type="text" value="{{ old('password') }}" placeholder="Captcha" class="uk-width-1-1 uk-form-small" required="required" />
                    </div>
                    <div class="uk-inline-middle uk-width-1-3 uk-float-right">
                        <a href="{{route('captcha.refresh')}}" id="refresh-captcha">
                            <img class="captcha" src="{{ captcha_src() }}" />
                        </a>
                    </div>
                </div>
                <div class="uk-form-row uk-text-left">
                    <a class="uk-width-1-3 uk-link uk-link-muted uk-text-small" href="{{route('auth.password.request')}}">Forgot Password ?</a>
                    <button class="uk-width-1-3 uk-button uk-button-primary uk-button-small uk-float-right">Sign In</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
var getTimezone = function(){
    return -((new Date()).getTimezoneOffset()/60);
}

$(document).ready(function() {
    $('input[name="timezone"]').val(getTimezone());
});
</script>
@endpush