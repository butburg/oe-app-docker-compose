<?php

namespace App\Mail;

use App\Models\PendingRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmRegistration extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $name, public string $url)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirm your email to create your account',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.confirm-registration',
            with: ['minutes' => PendingRegistration::VALID_MINUTES],
        );
    }
}
