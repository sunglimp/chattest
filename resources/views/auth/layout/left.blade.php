<div class="login__logo">
    <img src="{{asset('images/logo_small.png')}}" alt="logo" class="logo__img">
</div>
<div class="login__content">
    <div class="login__header">
                <span class="login__header--main">Welcome
                    <img src="{{asset('images/hand.png')}}" alt="wave">
                </span>
        <!-- <span class="login__header--sub">
                    To keep connected with us keep with your personal information
                    by email address and password
                    <img src="{{asset('images/smiley.png')}}" alt="smiley">
                </span> -->
    </div>

    @yield('main-section')

</div>
