<?php

namespace App\Jobs;

use App\Mail\VerifyRegister;
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
    private $otp;
    public function __construct(string $email, string $otp)
    {
        //
        $this->email = $email;
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Mail::to($this->email)->send(new VerifyRegister());
    }
}
