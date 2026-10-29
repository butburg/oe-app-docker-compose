<?php

namespace App\Actions;

use App\Jobs\SendMailBundle;
use App\Models\AdminMail;
use App\Models\Comment;
use App\Models\PendingMail;
use App\Models\Post;
use App\Models\User;

/**
 * Every mail to a user goes through here (FEAT-020): the item is stored and sent with the next mail bundle of
 * the recipient. The first item of a user starts the clock, after mail.bundle_minutes everything collected
 * until then goes out in one mail. Mails about the account (registration, email change) do not wait.
 */
class CollectMail
{
    /**
     * The post owner learns about a new comment, not about their own.
     */
    public function comment(Comment $comment): void
    {
        $owner = $comment->post?->user;
        if (!$owner || $owner->id === $comment->user_id || !$owner->hasVerifiedEmail() || !$owner->notify_comments) {
            return;
        }

        $this->collect([$owner->id], ['type' => PendingMail::COMMENT, 'comment_id' => $comment->id]);
    }

    /**
     * The other verified users learn about the first publication of a post, a republished post never again.
     */
    public function newImage(Post $post): void
    {
        if ($post->notified_at !== null) {
            return;
        }

        $recipients = User::whereNotNull('email_verified_at')
            ->where('notify_new_images', true)
            ->where('id', '!=', $post->user_id)
            ->orderBy('id')
            ->pluck('id');

        $this->collect($recipients->all(), ['type' => PendingMail::NEW_IMAGE, 'post_id' => $post->id]);
        $post->forceFill(['notified_at' => now()])->saveQuietly();
    }

    /**
     * Announcements go to everyone in the chosen group, the notification switches do not apply.
     *
     * @param iterable<User> $users
     */
    public function announcement(AdminMail $adminMail, iterable $users): void
    {
        $ids = collect($users)->pluck('id')->all();

        $this->collect($ids, ['type' => PendingMail::ANNOUNCEMENT, 'admin_mail_id' => $adminMail->id]);
    }

    /**
     * Users that had nothing waiting get a bundle job. Jobs of one call are spread over the minutes,
     * at most mail.per_minute per minute, so the SMTP provider's limit is never hit.
     * Items long overdue have lost their job, so these users get a new one instead of waiting forever.
     *
     * @param int[] $userIds
     */
    private function collect(array $userIds, array $item): void
    {
        if ($userIds === []) {
            return;
        }

        $now = now();
        $overdue = $now->copy()->subMinutes(SendMailBundle::minutes() + SendMailBundle::GRACE_MINUTES);
        $waiting = PendingMail::whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->havingRaw('MIN(created_at) > ?', [$overdue])
            ->pluck('user_id')->all();

        foreach (array_chunk($userIds, 500) as $chunk) {
            PendingMail::insert(array_map(fn ($id) => $item + [
                'user_id' => $id, 'comment_id' => null, 'post_id' => null, 'admin_mail_id' => null, 'created_at' => $now,
            ], $chunk));
        }

        $perMinute = max(1, (int) config('mail.per_minute'));
        foreach (array_values(array_diff($userIds, $waiting)) as $index => $userId) {
            SendMailBundle::dispatch($userId)
                ->delay($now->copy()->addMinutes(SendMailBundle::minutes() + intdiv($index, $perMinute)));
        }
    }
}
