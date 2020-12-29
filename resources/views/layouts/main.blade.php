<!DOCTYPE html>
<html lang="en" class="uk-height-1-1">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="chrome=1">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{url(mix('dist/css/all.css'))}}" rel="stylesheet">
    <title>{!! $title ?? env('APP_NAME',"SMS API DASHBOARD") !!}</title>
    @stack('styles')
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="sidebar_active uk-height-1-1">
    @include('layouts.header')

    <div class="section-container">
        <div class="section-content">
            <div class="content">
                @include('layouts.section-head')
                @yield('content')
            </div>
        </div>
        <div class="section-footer uk-text-small">
          <img src="{{ asset('images/logo/firstwap.svg') }}" alt=""> &copy; {{ date('Y') }} - <a href="mailto:techsupport@1rstwap.com">techsupport@1rstwap.com</a> | +62 21 2295 0041
        </div>
        @include('layouts.sidemenu')
    </div>
    @include('components.lang')
    <script type="text/javascript" src="{{ url(mix('dist/js/all.js')) }}"></script>
    @stack('scripts')
</body>

</html>