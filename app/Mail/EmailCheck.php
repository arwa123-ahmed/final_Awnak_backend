<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailCheck extends Mailable
{
    use Queueable, SerializesModels;

    public $link;

    public function __construct($link)
    {
        $this->link = $link;
    }

    public function build()
    {
        $bgColor = '#00bc7d';
        $link = $this->link;

        $html = '
        <div style="background-color: ' . $bgColor . '; padding: 30px; font-family: Arial, sans-serif; color: #fff; text-align: center;">

            <h1 style="
                font-size: 48px;
                margin-bottom: 10px;
                font-weight: bold;
                letter-spacing: 3px;
                font-family: Tahoma, sans-serif;
                text-shadow: 0px 0px 5px #ffffff70;
            ">
                عـــونـــك
            </h1>

            <h2>Reset Your Password</h2>

            <p style="font-size:16px; line-height:1.6;">
                You requested to reset your password for <b>Awnak</b>.<br>
                Click the button below to set a new password.<br>
                This link will expire in <b>15 minutes</b>.
            </p>

            <a href="' . $link . '"
               style="
                    display:inline-block;
                    margin-top:25px;
                    padding:15px 30px;
                    background-color:#ffffff;
                    color:#00bc7d;
                    font-size:18px;
                    font-weight:bold;
                    text-decoration:none;
                    border-radius:8px;
               ">
                Reset Password
            </a>

            <p style="font-size:14px; margin-top:25px;">
                If you did not request a password reset, please ignore this email.
            </p>

            <h5 style="margin-top:30px;">by Awnak team</h5>
        </div>
        ';

        return $this
            ->subject('Reset Your Password')
            ->html($html);
    }
}
