@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Verify Code & Reset Password') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <p class="text-muted">
                            We've sent a 6-digit verification code to <strong>{{ $email }}</strong>. 
                            Enter the code below along with your new password.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('password.reset-with-otp') }}" id="otpResetForm" class="needs-validation" novalidate>
                        @csrf

                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="row mb-3">
                            <label for="otp" class="col-md-4 col-form-label text-md-end">{{ __('Verification Code') }}</label>

                            <div class="col-md-6">
                                <input id="otp" type="text" class="form-control @error('otp') is-invalid @enderror" 
                                       name="otp" value="{{ old('otp') }}" required autocomplete="off" autofocus
                                       maxlength="6" pattern="[0-9]{6}" placeholder="000000">

                                @error('otp')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                
                                <small class="form-text text-muted">Enter the 6-digit code sent to your email</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('New Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" 
                                       name="password_confirmation" required autocomplete="new-password">
                                <div id="passwordMatchFeedback" class="form-text text-danger d-none">
                                    Passwords do not match.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    {{ __('Reset Password') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <form method="POST" action="{{ route('password.resend-otp') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="email" value="{{ $email }}">
                                <button type="submit" class="btn btn-link p-0">
                                    {{ __('Resend Code') }}
                                </button>
                            </form>

                            <a class="btn btn-link" href="{{ route('password.request-otp') }}">
                                {{ __('Use Different Email') }}
                            </a>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Important</h6>
                            <ul class="mb-0 small">
                                <li>The verification code expires in 10 minutes</li>
                                <li>You have a limited number of attempts</li>
                                <li>If you don't receive the code, check your spam folder</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const otpInput = document.getElementById('otp');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password-confirm');
    const passwordMatchFeedback = document.getElementById('passwordMatchFeedback');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('otpResetForm');
    
    // Auto-format OTP input
    otpInput.addEventListener('input', function(e) {
        // Only allow numbers
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Limit to 6 digits
        if (this.value.length > 6) {
            this.value = this.value.slice(0, 6);
        }
    });
    
    // Auto-focus to password field when 6 digits are entered
    otpInput.addEventListener('input', function(e) {
        if (this.value.length === 6) {
            passwordInput.focus();
        }
    });
    
    // Function to check if passwords match
    function checkPasswordsMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword === '') {
            passwordMatchFeedback.classList.add('d-none');
            confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
            submitBtn.disabled = false;
            return;
        }
        
        if (password === confirmPassword) {
            passwordMatchFeedback.classList.add('d-none');
            confirmPasswordInput.classList.remove('is-invalid');
            confirmPasswordInput.classList.add('is-valid');
            submitBtn.disabled = false;
        } else {
            passwordMatchFeedback.classList.remove('d-none');
            confirmPasswordInput.classList.remove('is-valid');
            confirmPasswordInput.classList.add('is-invalid');
            submitBtn.disabled = true;
        }
    }
    
    // Add event listeners for password validation
    passwordInput.addEventListener('input', checkPasswordsMatch);
    confirmPasswordInput.addEventListener('input', checkPasswordsMatch);
    
    // Form validation on submit
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (password !== confirmPassword) {
            event.preventDefault();
            passwordMatchFeedback.classList.remove('d-none');
            confirmPasswordInput.classList.add('is-invalid');
        }
        
        form.classList.add('was-validated');
    });
});
</script>
@endsection
