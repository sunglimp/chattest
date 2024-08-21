@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

<!DOCTYPE html>
<html>
    @include('layouts.head')
    <body class="{{ $languageClass }}">
        {{--Loader--}}
        <div class="loader {{ $languageClass }}">
            <div class="loader__spinner"></div>
        </div>
        {{--notification--}}
        <div class="notifier {{ $languageClass }}">
            <span class="notifier__icon  {{ $languageClass }}"></span>
            <span class="notifier__text"></span>
        </div>
        <div class="surbo-container">
            @include('layouts.side-bar')
            <section class="main {{ $languageClass }}"  @if(Auth::user()->role_id !=1 && Auth::user()->language =="ar") dir="rtl" @endif >
                @include('layouts.header')
                @yield('main-content')
            </section>  
        </div> 
        @include('layouts.script')
    </body>
</html>
