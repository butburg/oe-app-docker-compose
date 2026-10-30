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
 * Announcement from the admins (FEAT-017), used for the preview and "Send test to me". Sent to users as part of
 * their mail bundle (FEAT-020). The notification switches do not apply: it may contain security-relevant information.
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
        return new Envelope(subject: $this->subjectText());
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-message',
            with: ['html' => $this->bodyHtml()],
        );
    }

    /**
     * Subject with the placeholders filled, also used as part of the mail bundle subject (FEAT-020).
     */
    public function subjectText(): string
    {
        return strtr($this->mailSubject, $this->values + ['{website}' => config('app.url')]);
    }

    /**
     * Text as HTML with the placeholders filled, also used in the mail bundle (FEAT-020).
     */
    public function bodyHtml(): string
    {
        // {website} comes from the config, not from a user: filled before the Markdown step, so a bare
        // {website} and [text]({website}) both become links
        $body = str_replace('{website}', config('app.url'), $this->body);

        // Markdown from the admin form, raw HTML is shown as text and unsafe links are dropped.
        // User placeholders are filled after the Markdown step and escaped, so a name cannot add formatting or HTML.
        $html = Str::markdown($body, ['html_input' => 'escape', 'allow_unsafe_links' => false]);

        return strtr($html, array_map('e', $this->values));
    }
}
