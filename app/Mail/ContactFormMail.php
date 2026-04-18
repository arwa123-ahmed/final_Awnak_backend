<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class ContactFormMail extends Mailable
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }




    public function build()
{
    $data = $this->data;

    $html = '
    <div style="background:#f4f4f4; padding:40px; font-family:Arial,sans-serif;">

        <div style="max-width:600px; margin:auto; background:#ffffff; border-radius:10px; overflow:hidden;">

            <!-- Header -->
            <div style="background:#00bc7d; padding:25px; text-align:center; color:#fff;">
                <h1 style="margin:0; font-size:28px;">New Contact Message</h1>
            </div>

            <!-- Body -->
            <div style="padding:25px; color:#333;">

                <h2 style="margin-top:0; color:#00bc7d;">Message Details</h2>

                <div style="margin-bottom:15px;">
                    <strong>Name:</strong>
                    <div style="margin-top:5px; padding:10px; background:#f8f8f8; border-radius:6px;">
                        ' . $data['name'] . '
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <strong>Email:</strong>
                    <div style="margin-top:5px; padding:10px; background:#f8f8f8; border-radius:6px;">
                        ' . $data['email'] . '
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <strong>Subject:</strong>
                    <div style="margin-top:5px; padding:10px; background:#f8f8f8; border-radius:6px;">
                        ' . $data['subject'] . '
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <strong>Message:</strong>
                    <div style="margin-top:5px; padding:10px; background:#f8f8f8; border-radius:6px; line-height:1.6;">
                        ' . nl2br($data['message']) . '
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div style="background:#f0f0f0; padding:15px; text-align:center; font-size:12px; color:#777;">
                Contact Form Notification - Awnak
            </div>

        </div>

    </div>
    ';

    //return $this
      //  ->subject('New Contact Message')
        //->html($html);

    return $this->subject('New Contact Message')
        ->from(env('MAIL_FROM_ADDRESS'), 'Awnak Website')
        ->replyTo($this->data['email'], $this->data['name'])
        ->html($html);

}
}