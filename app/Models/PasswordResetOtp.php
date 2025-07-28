<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'attempts',
        'used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean'
    ];

    /**
     * Generate a 6-digit OTP
     */
    public static function generateOtp(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new OTP for the given email
     */
    public static function createForEmail(string $email): self
    {
        // Delete any existing OTPs for this email
        self::where('email', $email)->delete();

        return self::create([
            'email' => $email,
            'otp' => self::generateOtp(),
            'expires_at' => Carbon::now()->addMinutes(10), // OTP expires in 10 minutes
            'attempts' => 0,
            'used' => false
        ]);
    }

    /**
     * Verify OTP for the given email
     */
    public static function verifyOtp(string $email, string $otp): bool
    {
        $otpRecord = self::where('email', $email)
            ->where('otp', $otp)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRecord) {
            // Increment attempts for any non-expired OTP for this email
            self::where('email', $email)
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->increment('attempts');
            
            return false;
        }

        // Mark as used
        $otpRecord->update(['used' => true]);
        
        return true;
    }

    /**
     * Check if email has exceeded maximum attempts
     */
    public static function hasExceededAttempts(string $email, int $maxAttempts = 5): bool
    {
        $otpRecord = self::where('email', $email)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        return $otpRecord && $otpRecord->attempts >= $maxAttempts;
    }

    /**
     * Clean up expired OTPs
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', Carbon::now())->delete();
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is valid (not used, not expired, attempts not exceeded)
     */
    public function isValid(int $maxAttempts = 5): bool
    {
        return !$this->used && 
               !$this->isExpired() && 
               $this->attempts < $maxAttempts;
    }
}
