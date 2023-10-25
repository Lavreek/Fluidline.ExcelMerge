<!DOCTYPE html>
<html lang="ru">
    <head>
        @vite(['resources/css/app.css'])
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Excel Merge</title>
    </head>
    <body id="body">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-5">
                    @include('err')
                    @yield('content')
                </div>
            </div>
        </div>
    </body>
</html>
