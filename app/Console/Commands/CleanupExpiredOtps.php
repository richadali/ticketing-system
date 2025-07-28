<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PasswordResetOtp;

class CleanupExpiredOtps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired OTP records from the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $deletedCount = PasswordResetOtp::cleanupExpired();
        
        $this->info("Cleaned up {$deletedCount} expired OTP records.");
        
        return Command::SUCCESS;
    }
}
