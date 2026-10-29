<?php

namespace App\Mail;

use App\Models\User;
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

    /**
     * Placeholders the admin can use in subject and text, filled per recipient.
     */
    public const PLACEHOLDERS = [
        '{name}' => 'name of the user',
        '{email}' => 'email of the user',
        '{posts}' => 'number of their images',
        '{comments}' => 'number of their comments',
        '{website}' => 'link to the gallery',
    ];

    /**
     * @param array<string, string> $values placeholder => value for this recipient, see forUser()
     */
    public function __construct(public string $mailSubject, public string $body, public array $values = [])
    {
    }

    /**
     * The mail for one recipient, with the placeholders filled with their data.
     */
    public static function forUser(string $subject, string $body, User $user): self
    {
        return new self($subject, $body, [
            '{name}' => $user->name,
            '{email}' => $user->email,
            '{posts}' => (string) $user->posts()->count(),
            '{comments}' => (string) $user->comments()->count(),
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: strtr($this->mailSubject, $this->values + ['{website}' => config('app.url')]));
    }

    public function content(): Content
    {
        // {website} comes from the config, not from a user: filled before the Markdown step, so a bare
        // {website} and [text]({website}) both become links
        $body = str_replace('{website}', config('app.url'), $this->body);

        // Markdown from the admin form, raw HTML is shown as text and unsafe links are dropped.
        // User placeholders are filled after the Markdown step and escaped, so a name cannot add formatting or HTML.
        $html = Str::markdown($body, ['html_input' => 'escape', 'allow_unsafe_links' => false]);

        return new Content(
            markdown: 'emails.admin-message',
            with: ['html' => strtr($html, array_map('e', $this->values))],
        );
    }
}
