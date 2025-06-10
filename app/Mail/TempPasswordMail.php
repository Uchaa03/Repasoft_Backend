<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TempPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tempPassword;

    public function __construct($tempPassword)
    {
        $this->tempPassword = $tempPassword;
    }

    public function build()
    {
        return $this->view('emails.temp-password-html')
            ->subject('Contraseña temporal');
    }
}

