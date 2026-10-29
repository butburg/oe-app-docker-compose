<?php

namespace App\Mail;

use App\Mail\Concerns\EscapesMarkdown;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Tells the owner of a post that someone commented on it (FEAT-018). Sent by the SendNewCommentMail job.
 */
class NewComment extends Mailable
{
    use EscapesMarkdown, Queueable, SerializesModels;

    public const EXCERPT_LENGTH = 200;

    public function __construct(public Comment $comment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "{$this->comment->user->name} commented on your image");
    }

    /**
     * Mail clients can show an unsubscribe link: it leads to the settings (login needed, no one-click).
     */
    public function headers(): Headers
    {
        return new Headers(text: ['List-Unsubscribe' => '<' . route('profile.notifications') . '>']);
    }

    public function content(): Content
    {
        $post = $this->comment->post;

        return new Content(
            markdown: 'emails.new-comment',
            with: [
                'ownerName' => self::plainText($post->user->name),
                'commenterName' => self::plainText($this->comment->user->name),
                'postTitle' => self::plainText($post->title),
                'excerpt' => self::plainText(Str::limit($this->comment->comment, self::EXCERPT_LENGTH)),
                // the page of the post is computed now, when the mail is sent
                'url' => $post->is_published ? $post->galleryUrl() : route('posts.index'),
                'settingsUrl' => route('profile.notifications'),
            ],
        );
    }
}
