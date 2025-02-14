<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtp extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $otp_code;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $otp_code)
    {
        $this->user = $user;
        $this->otp_code = $otp_code;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your OTP Verification Code')
                    ->view('Mails.otp'); // Refers to the blade template for the email
    }
}
