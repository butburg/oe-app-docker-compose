<?php

namespace App\Jobs;

use App\Mail\NewComment;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Mails the post owner about a new comment (FEAT-018). Everything is checked when the job runs, not when the
 * comment is written: a comment or post deleted in the meantime, or a switched-off setting, sends nothing.
 */
class SendNewCommentMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public int $commentId)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $comment = Comment::with('user', 'post.user')->find($this->commentId);
        $owner = $comment?->post?->user;

        if (!$comment?->user || !$owner
            || $owner->is($comment->user)
            || !$owner->hasVerifiedEmail()
            || !$owner->notify_comments) {
            return;
        }

        Mail::to($owner)->send(new NewComment($comment));
    }
}
