<?php

namespace App\Models;

use App\Mail\AccountAlreadyExists;
use App\Mail\ConfirmRegistration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PendingRegistration extends Model
{
    /**
     * How long the link in the confirmation mail stays valid.
     */
    public const VALID_MINUTES = 60;

    /**
     * How many mails one address gets per hour, over register, resend and email change.
     * The IP throttles alone would let anyone flood a foreign address with mails.
     */
    public const MAILS_PER_HOUR = 7;

    protected $fillable = ['name', 'email', 'password', 'token', 'expires_at'];

    protected $hidden = ['password', 'token'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Find the not yet expired registration for a token from a mail link.
     */
    public static function findByToken(string $token): ?self
    {
        return static::where('token', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Registrations are rare, so expired ones are cleaned up on the next registration
     * instead of by a cron job. Until then their links are rejected by findByToken.
     */
    public static function deleteExpired(): void
    {
        static::where('expires_at', '<=', now())->delete();
    }

    /**
     * Checked before anything is changed, so a blocked request also keeps the
     * pending registration of the address and its link working.
     *
     * @throws ValidationException
     */
    public static function ensureCanSendTo(string $email): void
    {
        if (RateLimiter::tooManyAttempts(self::mailLimitKey($email), self::MAILS_PER_HOUR)) {
            throw ValidationException::withMessages([
                'email' => __('Too many mails to this address, please try again later.'),
            ]);
        }
    }

    private static function mailLimitKey(string $email): string
    {
        return 'register-mail:' . $email;
    }

    /**
     * Create a new token (the previous link stops working) and mail it.
     * If the email already has an account, a hint mail is sent instead, so the page
     * looks the same either way and does not reveal which emails are registered.
     */
    public function sendLink(): void
    {
        $token = Str::random(64);
        $this->token = hash('sha256', $token);
        $this->expires_at = now()->addMinutes(self::VALID_MINUTES);
        $this->save();

        RateLimiter::hit(self::mailLimitKey($this->email), 3600);

        if (User::where('email', $this->email)->exists()) {
            Mail::to($this->email)->send(new AccountAlreadyExists());
            return;
        }

        Mail::to($this->email)->send(new ConfirmRegistration($this->name, route('register.confirm', $token)));
    }
}
