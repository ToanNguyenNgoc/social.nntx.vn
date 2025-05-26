<?php

namespace App\Jobs;

use App\Mail\VerifyRegister;
use App\Models\Otp;
use App\Utils\CommonUtils;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class VerifyRegisterMail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    private $email;
    public function __construct(string $email)
    {
        //
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $otp_code = CommonUtils::generateOTP(6);
        Otp::create([
            'email' => $this->email,
            'otp' => $otp_code,
            'expired_at' => Carbon::now()->addMinutes(3)
        ]);
        Mail::to($this->email)->send(new VerifyRegister($this->email, $otp_code));
    }
}
