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
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        // Mail::to('toan@myspa.vn')->send(new VerifyRegister());
    }
}
