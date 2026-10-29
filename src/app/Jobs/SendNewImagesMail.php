<?php

namespace App\Jobs;

use App\Actions\QueueMails;
use App\Mail\NewImages;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

/**
 * Bundles the publications of one uploader into one mail per recipient (FEAT-019), without cron:
 * every publication dispatches this job with a delay of mail.new_images_quiet_minutes. When it runs and the
 * uploader has published again since, it does nothing; the job of the newest publication sends for all of them.
 */
class SendNewImagesMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public int $uploaderId, public string $publishedAt)
    {
        $this->afterCommit();
    }

    /**
     * Called after a post was published for the first time.
     */
    public static function forPublication(Post $post): void
    {
        static::dispatch($post->user_id, $post->published_at->toDateTimeString())
            ->delay(now()->addMinutes((int) config('mail.new_images_quiet_minutes')));
    }

    public function handle(QueueMails $queueMails): void
    {
        // one job per uploader at a time, so two jobs never send the same posts
        Cache::lock("new-images-mail:{$this->uploaderId}", 60)->block(10, function () use ($queueMails) {
            $posts = $this->pendingPosts();

            // drafts and deleted posts are not in the list anymore; nothing left, no mail
            if ($posts->isEmpty() || $posts->max('published_at')->toDateTimeString() > $this->publishedAt) {
                return;
            }

            $recipients = User::whereNotNull('email_verified_at')
                ->where('notify_new_images', true)
                ->where('id', '!=', $this->uploaderId)
                ->orderBy('id')
                ->get();

            $uploaderName = $posts->first()->user?->name ?? $posts->first()->username;
            $titles = $posts->pluck('title')->all();
            $queueMails->handle($recipients, fn () => new NewImages($uploaderName, $titles));

            Post::whereIn('id', $posts->pluck('id'))->update(['notified_at' => now()]);
        });
    }

    private function pendingPosts()
    {
        return Post::with('user')
            ->where('user_id', $this->uploaderId)
            ->where('is_published', true)
            ->whereNull('notified_at')
            ->orderBy('published_at')
            ->get();
    }
}
