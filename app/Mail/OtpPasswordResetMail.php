<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $email;
    public $expiresAt;

    /**
     * Create a new message instance.
     *
     * @param string $otp
     * @param string $email
     * @param \Carbon\Carbon $expiresAt
     * @return void
     */
    public function __construct($otp, $email, $expiresAt)
    {
        $this->otp = $otp;
        $this->email = $email;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Password Reset OTP - ' . config('app.name'))
                    ->view('emails.otp-password-reset')
                    ->with([
                        'otp' => $this->otp,
                        'email' => $this->email,
                        'expiresAt' => $this->expiresAt,
                        'appName' => config('app.name')
                    ]);
    }
}
