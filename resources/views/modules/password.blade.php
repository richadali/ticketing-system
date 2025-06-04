@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Reset Password') }}</div>

                <div class="card-body">
                    <form method="POST" id="form-reset-password">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token ?? ''}}">

                        <div class="row mb-3" style="display:none;">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus readonly>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div id="reset_content">
                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                            <span id="valid_password" style="display:none;"><p style=color:red>Password does not match</p></span>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">{{ __('Reset Password') }}</button>
                            </div>
                        </div>
                        </div>
                        <br>
                    </form>
                    <div id="main_check" class="alert alert-success alert-dismissible fade show" role="alert" style="display:none;">
                                <h4 class="alert-heading">Your password is reset</h4>
                                <hr>
                                <p class="mb-0">Go back to your mobile application and login in.</p>
                        </div>
                        <div id="main_check_error" class="alert alert-danger alert-dismissible fade show" role="alert" style="display:none;">
                                <h4 class="alert-heading">Application Error</h4>
                                <hr>
                                <p class="mb-0">Contact System administrator @ <u><i>brandonmarbs@gmail.com</i></u></p>
                        </div>
                        <div id="main_check_error_validation" class="alert alert-danger alert-dismissible fade show" role="alert" style="display:none;">
                                <h4 class="alert-heading">Validation Error</h4>
                                <hr>
                                <p class="mb-0">Password should be atleast 8 characters</p>
                                <button type="button" class="btn-close get-content" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
