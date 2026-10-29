<?php

namespace App\Mail;

use App\Mail\Concerns\EscapesMarkdown;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * "Anna published 4 new images" (FEAT-019). No images in the mail (small, no sensitive images in inboxes,
 * image links expire anyway) and no links except the gallery and the mail settings.
 */
class NewImages extends Mailable
{
    use EscapesMarkdown, Queueable, SerializesModels;

    public const MAX_TITLES = 10;

    /**
     * @param string[] $titles
     */
    public function __construct(public string $uploaderName, public array $titles)
    {
    }

    public function envelope(): Envelope
    {
        $count = count($this->titles);

        return new Envelope(subject: "{$this->uploaderName} published {$count} new " . Str::plural('image', $count));
    }

    public function headers(): Headers
    {
        return new Headers(text: ['List-Unsubscribe' => '<' . route('profile.notifications') . '>']);
    }

    public function content(): Content
    {
        $count = count($this->titles);

        return new Content(
            markdown: 'emails.new-images',
            // own names: public properties go into the view after "with" and would replace the escaped values
            with: [
                'uploaderText' => self::plainText($this->uploaderName),
                'count' => $count,
                'images' => Str::plural('image', $count),
                'titleTexts' => array_map(self::plainText(...), array_slice($this->titles, 0, self::MAX_TITLES)),
                'more' => max(0, $count - self::MAX_TITLES),
                'settingsUrl' => route('profile.notifications'),
            ],
        );
    }
}
