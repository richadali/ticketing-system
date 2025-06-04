@extends('layouts.app')

@section('content')

<main class="d-flex align-items-center justify-content-center vh-100 custom-background">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-8">
                <!-- Adjusted column widths for responsiveness -->
                <div class="card shadow-lg">
                    <div class="card-body">
                        <div class="row">
                            <!-- Logo and System Name Section -->
                            <div class="col-md-6 text-center mt-5">
                                <img src="{{ asset('/assets/img/logo.svg') }}" alt="Department Logo"
                                    style="max-width: 180px; height: auto;">
                                <h3 class="mt-2"><br>Ticketing System</h3>
                            </div>

                            <!-- Login Form Section -->
                            <div class="col-md-6">
                                <h5 class="card-title text-center pb-3">Login to Your Account</h5>
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="email" class="form-label">{{ __('Email') }}</label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text" id="inputGroupPrepend">@</span>
                                            <input id="email" type="email"
                                                class="form-control @error('email') is-invalid @enderror" name="email"
                                                value="{{ old('email') }}" required autocomplete="email" autofocus>
                                            <div class="invalid-feedback">Please enter your email address</div>
                                            @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">{{ __('Password') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                                            <input id="password" type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                name="password" required autocomplete="current-password">
                                            <div class="invalid-feedback">Please enter your password!</div>
                                            @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary w-100">
                                            {{ __('Login') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        Designed and Developed by Artec Engine
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@endsection