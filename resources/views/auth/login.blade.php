@extends('auth.login-app')
@section('auth-title','Login')
@section('main-section')

<form method="POST" action="{{ route('login') }}" autocomplete="off">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="login">
        @if(count($errors))
            @foreach ($errors->all() as $error)
                <span class="warning-text" style="max-width: 335px; margin-bottom: 10px;">{{$error}}</span>
            @endforeach
        @endif
        <label class="login__inputs" id="input-email">
            <div class="login__inputs--icon">
                <i class="far fa-envelope"></i>
            </div>
            <div class="login__inputs--data">
                <label for="input-email" class="login__label">Email Address</label>
                {{--<input type="email" class="custom-input custom-input-hidden" id="login-email" placeholder="enter your email">--}}
                <input  type="text"   id="login-email" class=" custom-input custom-input-hidden form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" autocomplete="false" required>

            </div>
            <div class="login__inputs--tick">
                <span class="icon--login icon--check" id="email-isvalid"><i class="fas fa-check"></i></span>
            </div>
        </label>
        <label class="login__inputs margin-bottom-2" id="input-password">
            <div class="login__inputs--icon">
                <i class="fas fa-lock"></i>
            </div>
            <div class="login__inputs--data">
                <label for="input-password" class="login__label">Password</label>

                <input id="password" type="password" class="custom-input custom-input-hidden form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>


                {{--<input type="password" class="custom-input custom-input-hidden" placeholder="enter your password">--}}
            </div>
            <div class="login__inputs--tick">
                <span class="icon--login icon--cross"><i class="fas fa-times"></i></span>
            </div>
        </label>
        <div class="login__features margin-bottom-2">
            <label class="custom-checkbox margin-right-half">
                <input type="checkbox" name="remember" checked hidden>

                {{--<span class="disable-selection">Remember me</span>--}}
            </label>
<!--            <div class="login__forgot disable-selection"><a href={{route("password.request")}}>Forgot password?</a></div>-->
        </div>
        <div class="login__button">
            <button type="submit" class="btn btn-primary custom-button custom-button-lightgreen">
                Login<span class="icon-login-button "><i class="fas fa-arrow-right"></i></span>
            </button>
        </div>
    </div>
</form>
@endsection

