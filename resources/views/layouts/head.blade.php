<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon.png')}}"/>
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset("fonts/fontawesome/css/all.min.css")}}" />
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset("css/perfect-scrollbar.css")}}" />
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset("css/daterangepicker.css")}}" />
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset("css/dcalendar.picker.css")}}" />
    <link rel="stylesheet" href="{{ asset("css/dataTables.min.css")}}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ mix("css/main.css") }}"  />
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.5.5/slick.min.css'>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    <script type="text/javascript">
        var APP_URL = {!! json_encode(url('/')) !!}
    </script>
</head>
