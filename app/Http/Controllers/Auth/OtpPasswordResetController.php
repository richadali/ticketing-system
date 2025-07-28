<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Mail\OtpPasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class OtpPasswordResetController extends Controller
{
    /**
     * Show the form for requesting a password reset OTP
     */
    public function showRequestForm()
    {
        return view('auth.passwords.request-otp');
    }

    /**
     * Send OTP to user's email
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $email = $request->email;

        // Rate limiting: Allow only 3 OTP requests per email per hour
        $key = 'otp-request:' . $email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many OTP requests. Please try again in " . ceil($seconds / 60) . " minutes."
            ])->withInput();
        }

        try {
            // Clean up any expired OTPs
            PasswordResetOtp::cleanupExpired();

            // Create new OTP
            $otpRecord = PasswordResetOtp::createForEmail($email);

            // Send OTP email
            Mail::to($email)->send(new OtpPasswordResetMail(
                $otpRecord->otp,
                $email,
                $otpRecord->expires_at
            ));

            // Increment rate limiter
            RateLimiter::hit($key, 3600); // 1 hour

            return redirect()->route('password.verify-otp')
                           ->with('email', $email)
                           ->with('success', 'OTP has been sent to your email address.');

        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'Failed to send OTP. Please try again later.'
            ])->withInput();
        }
    }

    /**
     * Show the form for verifying OTP and resetting password
     */
    public function showVerifyForm(Request $request)
    {
        $email = $request->session()->get('email') ?? $request->get('email') ?? old('email');
        
        if (!$email) {
            return redirect()->route('password.request-otp')
                           ->withErrors(['email' => 'Please request an OTP first.']);
        }

        // Store email in session to persist through validation errors
        $request->session()->put('email', $email);

        return view('auth.passwords.verify-otp', compact('email'));
    }

    /**
     * Verify OTP and reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            // Preserve email in session for redirect
            $request->session()->put('email', $request->email);
            return redirect()->route('password.verify-otp')
                           ->withErrors($validator)
                           ->withInput($request->only('email'));
        }

        $email = $request->email;
        $otp = $request->otp;

        // Rate limiting for OTP verification attempts
        $key = 'otp-verify:' . $email;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $request->session()->put('email', $email);
            return redirect()->route('password.verify-otp')
                           ->withErrors([
                               'otp' => "Too many failed attempts. Please try again in " . ceil($seconds / 60) . " minutes."
                           ])->withInput($request->only('email'));
        }

        // Check if email has exceeded maximum attempts
        if (PasswordResetOtp::hasExceededAttempts($email)) {
            $request->session()->put('email', $email);
            return redirect()->route('password.verify-otp')
                           ->withErrors([
                               'otp' => 'Maximum verification attempts exceeded. Please request a new OTP.'
                           ])->withInput($request->only('email'));
        }

        // Verify OTP
        if (!PasswordResetOtp::verifyOtp($email, $otp)) {
            RateLimiter::hit($key, 900); // 15 minutes
            $request->session()->put('email', $email);
            return redirect()->route('password.verify-otp')
                           ->withErrors([
                               'otp' => 'Invalid or expired OTP. Please check and try again.'
                           ])->withInput($request->only('email'));
        }

        try {
            // Update user password
            $user = User::where('email', $email)->first();
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // Clean up used OTP and any other OTPs for this email
            PasswordResetOtp::where('email', $email)->delete();

            // Clear rate limiters
            RateLimiter::clear($key);
            RateLimiter::clear('otp-request:' . $email);

            return redirect()->route('login')
                           ->with('success', 'Password has been reset successfully. You can now login with your new password.');

        } catch (\Exception $e) {
            \Log::error('Failed to reset password: ' . $e->getMessage());
            $request->session()->put('email', $email);
            return redirect()->route('password.verify-otp')
                           ->withErrors([
                               'password' => 'Failed to reset password. Please try again.'
                           ])->withInput($request->only('email'));
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $email = $request->email;

        // Rate limiting for resend requests
        $key = 'otp-resend:' . $email;
        if (RateLimiter::tooManyAttempts($key, 2)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many resend requests. Please try again in " . ceil($seconds / 60) . " minutes."
            ]);
        }

        try {
            // Create new OTP (this will delete the old one)
            $otpRecord = PasswordResetOtp::createForEmail($email);

            // Send OTP email
            Mail::to($email)->send(new OtpPasswordResetMail(
                $otpRecord->otp,
                $email,
                $otpRecord->expires_at
            ));

            // Increment rate limiter
            RateLimiter::hit($key, 300); // 5 minutes

            return back()->with('success', 'New OTP has been sent to your email address.');

        } catch (\Exception $e) {
            \Log::error('Failed to resend OTP email: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'Failed to resend OTP. Please try again later.'
            ]);
        }
    }
}
