<?php

namespace App\Console\Commands;

use App\Models\EmailVerification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupExpiredOTPs extends Command
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
    protected $description = 'Clean up expired OTP verification records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deletedCount = EmailVerification::where('expires_at', '<', Carbon::now())
            ->orWhere('is_verified', true)
            ->delete();

        $this->info("Cleaned up {$deletedCount} expired/verified OTP records.");
        
        return 0;
    }
}