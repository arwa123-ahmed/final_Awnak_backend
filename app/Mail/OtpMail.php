<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function build()
    {
        $bgColor = '#00bc7d';
        $otp = $this->otp;

        $html = '
        <div style="background-color: ' . $bgColor . '; padding: 30px; font-family: Arial, sans-serif; color: #fff; text-align: center;">

            
            <h1 style="
                font-size: 48px;
                margin-bottom: 10px;
                font-weight: bold;
                letter-spacing: 3px;
                font-family: \'Tahoma\', sans-serif;
                text-shadow: 0px 0px 5px #ffffff70;
            ">
                عـــونـــك
            </h1>

            <h2>Verification Code</h2>
            <p style="font-size:16px; line-height:1.5;">
                Copy the code below to sign up to Awnak.<br>
                This code can only be used once and expires in 10 minutes.
            </p>

            <h1 style="font-size:36px; letter-spacing:2px; margin-top:20px;">' . $otp . '</h1>

            <p style="font-size:16px; line-height:1.5;">
                If the code is expired, try to sign up again.
            </p>

            <h5>by Awnak team</h5>
        </div>
        ';

        return $this->subject('Your Verification Code')->html($html);
    }
}
