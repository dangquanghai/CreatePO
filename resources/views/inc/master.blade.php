<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Master page</title>
    <link rel="stylesheet" href="{{asset('css/app.css')}}" >
    <script type="text/javascript" src="{{asset('js/app.js')}}"></script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>

    <link rel="stylesheet" href="https://kendo.cdn.telerik.com/2019.3.1023/styles/kendo.common.min.css"/>
    <link rel="stylesheet" href="https://kendo.cdn.telerik.com/2019.3.1023/styles/kendo.rtl.min.css"/>
    <link rel="stylesheet" href="https://kendo.cdn.telerik.com/2019.3.1023/styles/kendo.silver.min.css"/>
    <link rel="stylesheet" href="https://kendo.cdn.telerik.com/2019.3.1023/styles/kendo.mobile.all.min.css"/>

    <script src="https://kendo.cdn.telerik.com/2019.3.1023/js/jszip.min.js"></script>
    <script src="https://kendo.cdn.telerik.com/2019.3.1023/js/kendo.all.min.js"></script>
    
</head>
<body>
    @include('inc.navbar')
    @yield('content')
    @yield('styles')
    @yield('scripts')
</body>
</html>