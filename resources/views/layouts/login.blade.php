<!DOCTYPE html>
<html lang="en" class="uk-height-1-1">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="chrome=1">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
    <link href="{{url('dist/css/all.css')}}" rel="stylesheet">
    <title>{!! $title ?? env('APP_NAME',"SMS API DASHBOARD") !!}</title>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript" src="{{ url('dist/js/all.js')}}"></script>
    <script>
        $(document).ready(function() {
            var login = new LoginPage();

            login.bindEvent();
        });
    </script>
    @stack('scripts')

</head>

<body class="login-page uk-height-1-1">
    @include('layouts.header')

    <div class="section-content uk-height-1-1" style="background-image: url({{ url('images/bg/bg-login.jpg') }});">
        @yield('content')
    </div>

    <div class="section-footer uk-text-small">
        <img src="{{ asset('images/logo/firstwap.svg') }}" alt=""> &copy; {{ date('Y') }} - <a href="mailto:techsupport@1rstwap.com">techsupport@1rstwap.com</a> | +62 21 2295 0041
    </div>
</body>

</html>