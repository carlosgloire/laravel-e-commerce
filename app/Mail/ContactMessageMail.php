<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;

    public function __construct($contact)
    {
        $this->contact = $contact;
    }

    public function build() {
        // Attach the image (for CID reference if needed)
        return $this->subject('New Contact Message')
                   ->view('emails.contact_message')
                   ->attach(public_path('images/logo/logo.png'), [
                       'as'   => 'logo.png',
                       'mime' => 'image/png',
                   ]);
    }
}
