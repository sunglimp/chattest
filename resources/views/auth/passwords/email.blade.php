@extends('auth.login-app')
@section('auth-title','Reset')
@section('main-section')
<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="login">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        @if(count($errors))
            @foreach ($errors->all() as $error)
                <span class="warning-text">{{$error}}</span>
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
        <div class="login__button">


            <button type="submit" class="btn btn-primary custom-button custom-button-lightgreen">
                <span class="icon-login-button "><i class="fas fa-arrow-right"></i></span>
            </button>
        </div>
    </div>
</form>

@endsection
