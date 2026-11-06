<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otpCode;
    public $userName;

    public function __construct($otpCode, $userName)
    {
        $this->otpCode = $otpCode;
        $this->userName = $userName;
    }

    public function build()
    {
        return $this->subject('Kode OTP Reset Password - SCP School')
                    ->view('emails.otp_mail');
    }
}