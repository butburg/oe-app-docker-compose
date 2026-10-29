<?php

namespace App\Mail;

use App\Mail\Concerns\EscapesMarkdown;
use App\Models\PendingMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Everything collected for one user in one mail (FEAT-020): announcements, comments on their images and new images
 * of others. No images in the mail (small, no sensitive images in inboxes, image links expire anyway).
 * Text from users is shown as plain text, see EscapesMarkdown.
 */
class MailBundle extends Mailable
{
    use EscapesMarkdown, Queueable, SerializesModels;

    public const EXCERPT_LENGTH = 200;
    public const MAX_TITLES = 10;

    /**
     * @param AdminMessage[] $announcements
     * @param array<int, array{title: string, url: string, comments: array<int, array{name: string, text: string}>}> $comments per post
     * @param array<int, array{name: string, titles: string[]}> $uploads per uploader
     */
    public function __construct(public User $user, public array $announcements, public array $comments, public array $uploads)
    {
    }

    /**
     * The bundle from the waiting items, null if nothing is left to send. Everything is checked now, not when
     * the item was collected: a post made draft or a switched-off setting in the meantime drops the item.
     *
     * @param Collection<int, PendingMail> $items
     */
    public static function fromItems(User $user, Collection $items): ?self
    {
        $notify = fn (string $setting) => $user->hasVerifiedEmail() && $user->{$setting};

        $announcements = $items->where('type', PendingMail::ANNOUNCEMENT)->pluck('adminMail')->filter()->unique('id')
            ->map(fn ($adminMail) => AdminMessage::forUser($adminMail->subject, $adminMail->body, $user))
            ->values()->all();

        $comments = !$notify('notify_comments') ? [] : $items->where('type', PendingMail::COMMENT)->pluck('comment')
            ->filter(fn ($comment) => $comment?->user && $comment->post?->user_id === $user->id)
            ->groupBy('post_id')
            ->map(fn ($postComments) => [
                'title' => $postComments->first()->post->title,
                'url' => $postComments->first()->post->is_published ? $postComments->first()->post->galleryUrl() : route('posts.index'),
                'comments' => $postComments->map(fn ($comment) => [
                    'name' => $comment->user->name,
                    'text' => Str::limit($comment->comment, self::EXCERPT_LENGTH),
                ])->all(),
            ])->values()->all();

        $uploads = !$notify('notify_new_images') ? [] : $items->where('type', PendingMail::NEW_IMAGE)->pluck('post')
            ->filter(fn ($post) => $post?->is_published)
            ->groupBy('user_id')
            ->map(fn ($posts) => [
                'name' => $posts->first()->user?->name ?? $posts->first()->username,
                'titles' => $posts->pluck('title')->all(),
            ])->values()->all();

        if ($announcements === [] && $comments === [] && $uploads === []) {
            return null;
        }

        return new self($user, $announcements, $comments, $uploads);
    }

    public function envelope(): Envelope
    {
        $parts = array_map(fn (AdminMessage $announcement) => $announcement->subjectText(), $this->announcements);

        $commentCount = collect($this->comments)->sum(fn ($post) => count($post['comments']));
        if ($commentCount === 1) {
            $parts[] = "{$this->comments[0]['comments'][0]['name']} commented on your image";
        } elseif ($commentCount > 1) {
            $parts[] = "{$commentCount} new comments on your images";
        }

        $imageCount = collect($this->uploads)->sum(fn ($upload) => count($upload['titles']));
        if ($imageCount > 0) {
            $parts[] = count($this->uploads) === 1
                ? "{$this->uploads[0]['name']} published {$imageCount} new " . Str::plural('image', $imageCount)
                : "{$imageCount} new images from " . self::names(array_column($this->uploads, 'name'));
        }

        return new Envelope(subject: implode(' · ', $parts));
    }

    /**
     * Mail clients can show an unsubscribe link: it leads to the settings (login needed, no one-click).
     * Not for announcements only, they cannot be switched off.
     */
    public function headers(): Headers
    {
        return new Headers(text: $this->hasNotifications() ? ['List-Unsubscribe' => '<' . route('profile.notifications') . '>'] : []);
    }

    public function content(): Content
    {
        $switchedOn = array_keys(array_filter(['comments' => $this->comments !== [], 'new images' => $this->uploads !== []]));

        return new Content(
            markdown: 'emails.bundle',
            // own names: public properties go into the view after "with" and would replace the escaped values
            with: [
                'announcementHtml' => array_map(fn (AdminMessage $announcement) => $announcement->bodyHtml(), $this->announcements),
                'commentPosts' => array_map(fn ($post) => [
                    'title' => self::plainText($post['title']),
                    'url' => $post['url'],
                    'names' => self::plainText(self::names(array_unique(array_column($post['comments'], 'name')))),
                    'comments' => array_map(fn ($comment) => [
                        'name' => self::plainText($comment['name']),
                        'text' => self::plainText($comment['text']),
                    ], $post['comments']),
                ], $this->comments),
                'uploaders' => array_map(fn ($upload) => [
                    'name' => self::plainText($upload['name']),
                    'count' => count($upload['titles']),
                    'titles' => array_map(self::plainText(...), array_slice($upload['titles'], 0, self::MAX_TITLES)),
                    'more' => max(0, count($upload['titles']) - self::MAX_TITLES),
                ], $this->uploads),
                'imageCount' => collect($this->uploads)->sum(fn ($upload) => count($upload['titles'])),
                'switchedOn' => implode(' and ', $switchedOn),
                'settingsUrl' => route('profile.notifications'),
            ],
        );
    }

    private function hasNotifications(): bool
    {
        return $this->comments !== [] || $this->uploads !== [];
    }

    /**
     * "Anna", "Anna and Ben", "Anna, Ben and Carla", "Anna, Ben and 3 others"
     */
    private static function names(array $names): string
    {
        $names = array_values($names);
        if (count($names) > 3) {
            return "{$names[0]}, {$names[1]} and " . (count($names) - 2) . ' others';
        }
        $last = array_pop($names);

        return $names === [] ? $last : implode(', ', $names) . " and {$last}";
    }
}
