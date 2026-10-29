<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Announcement from the admins (FEAT-017). Goes to everyone in the chosen group, the notification
 * switches do not apply and it has no settings link: it may contain security-relevant information.
 */
class AdminMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $mailSubject, public string $body)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-message',
            with: [
                // Markdown from the admin form, raw HTML is shown as text and unsafe links are dropped
                'html' => Str::markdown($this->body, ['html_input' => 'escape', 'allow_unsafe_links' => false]),
            ],
        );
    }
}
