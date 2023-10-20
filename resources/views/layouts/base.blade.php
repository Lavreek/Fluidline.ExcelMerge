<!DOCTYPE html>
<html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
        <script src='../../js/form.js'></script>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="../../css/app.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
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
