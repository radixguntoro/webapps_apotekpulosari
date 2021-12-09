<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Apotek Pulosari - Point of Sales</title>

    <link rel="stylesheet" href="{{ asset('plugin/bower_components/toastr/build/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugin/bower_components/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/css/style.css') }}">
</head>

<body>
    <div class="login">
        <main>
            @yield('content')
        </main>
    </div>
    <!-- Scripts -->
    <!-- Jquery Libraries -->
    {{-- Scripts --}}
    <script src="{{ asset('plugin/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('plugin/bower_components/popper.js/dist/umd/popper.js') }}"></script>
    <script src="{{ asset('plugin/bower_components/chart.js/dist/Chart.min.js') }}"></script>
    <script src="{{ asset('plugin/bower_components/toastr/build/toastr.min.js') }}"></script>
    <script src="{{ asset('plugin/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- End Jquery Libraries -->
    <script>
        $(function () {
            var windowHeight = $(window).innerHeight();
            var cardHeight = $(".card").innerHeight();
            $("#app").css("height", windowHeight);
            $(".card-body.bg-gradient-primary").css("height", cardHeight);
        });

    </script>
</body>

</html>
