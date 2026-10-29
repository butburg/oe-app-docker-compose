<?php

namespace App\Actions;

use App\Models\User;
use Closure;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class QueueMails
{
    /**
     * Queue one mail per user (nobody sees the other addresses). Mails are spread over the minutes,
     * at most mail.per_minute per minute, so the SMTP provider's limit is never hit.
     *
     * @param iterable<User> $users
     * @param Closure(User): Mailable $makeMail
     * @return int number of queued mails
     */
    public function handle(iterable $users, Closure $makeMail): int
    {
        $perMinute = max(1, (int) config('mail.per_minute'));
        $count = 0;

        foreach ($users as $user) {
            $minute = intdiv($count, $perMinute);
            $mail = $makeMail($user);
            if ($minute > 0) {
                $mail->delay(now()->addMinutes($minute));
            }

            Mail::to($user)->queue($mail);
            $count++;
        }

        return $count;
    }
}
