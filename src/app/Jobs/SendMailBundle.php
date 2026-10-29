<?php

namespace App\Jobs;

use App\Mail\MailBundle;
use App\Models\PendingMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * Sends everything collected for one user in one mail (FEAT-020): CollectMail dispatches this job with a delay of
 * mail.bundle_minutes when the first item of a user arrives. Needs a worker (locally the queue container, on the
 * server a cron job at the hoster), not the sync queue: that ignores the delay.
 */
class SendMailBundle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    // here and not only on the worker's command line, a worker started with the defaults tries just once
    public $tries = 3;
    public $backoff = 60;

    /**
     * Items still waiting this long after their window are left over from a lost job (failed for good, jobs table
     * emptied): CollectMail then starts a new one. Covers the spreading over the minutes and the retries.
     */
    public const GRACE_MINUTES = 30;

    public function __construct(public int $userId)
    {
        $this->afterCommit();
    }

    public static function minutes(): int
    {
        return max(0, (int) config('mail.bundle_minutes'));
    }

    public function handle(): void
    {
        // one job per user at a time, so two jobs never send the same items
        Cache::lock("mail-bundle:{$this->userId}", 120)->block(10, function () {
            $user = User::find($this->userId);
            $items = PendingMail::with('comment.user', 'comment.post.user', 'post.user', 'adminMail')
                ->where('user_id', $this->userId)
                ->orderBy('created_at')->orderBy('id')
                ->get();

            if (!$user || $items->isEmpty()) {
                return;
            }

            // an extra job (e.g. two items arrived at the same moment) is too early: wait for the oldest item
            $due = $items->first()->created_at->copy()->addMinutes(self::minutes());
            if ($due->isFuture()) {
                static::dispatch($this->userId)->delay($due);
                return;
            }

            $mail = MailBundle::fromItems($user, $items);
            if ($mail) {
                Mail::to($user)->send($mail);
            }
            // only after sending: if sending fails, the job is retried with the same items
            PendingMail::whereIn('id', $items->pluck('id'))->delete();

            // items that arrived while this job ran found a waiting bundle and started no job of their own
            $next = PendingMail::where('user_id', $this->userId)->orderBy('created_at')->first();
            if ($next) {
                static::dispatch($this->userId)->delay($next->created_at->copy()->addMinutes(self::minutes()));
            }
        });
    }
}
