@php
$languageClass = '';
if (Auth::user()->language === 'ar') {
    $languageClass = 'arabic';
}
@endphp

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <base href="/chat">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title',"chat")</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" media="screen" href="{{mix("css/main.css") }}"  />
        <link rel="icon" type="image/x-icon" href="{{asset("images/favicon.png")}}">
        <link rel="stylesheet" type="text/css" media="screen" href="{{asset("fonts/fontawesome/css/all.min.css")}}" />
        <script type="text/javascript">
            var APP_URL = {!! json_encode(url('/')) !!}
            var USER = {!! json_encode($jsVar) !!}
            var TICKET_INTEGRATION_URL = {!! json_encode(config('tms.ticket_integration_url')) !!}
        </script>
    </head>
    <body>
        <div class="surbo-container">
            @include('layouts.side-bar')
            <section class="main {{ $languageClass }}"  @if(Auth::user()->role_id !=1 && Auth::user()->language =="ar") dir="rtl" @endif>
                @include('layouts.header')
                @yield('content')
            </section>
        </div>

        {!!$angularAssets!!}
    </body>
</html>
