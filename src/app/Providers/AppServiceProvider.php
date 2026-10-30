<?php

namespace App\Providers;

use App\Mail\MailAssets;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // so if i forgot fillable with attribute in the model it will throw an error now
        Model::preventSilentlyDiscardingAttributes();

        // @verified: logged in with a confirmed email, only then protected posts and comments are shown.
        // Logged in but unverified only happens for old accounts that were unverified before FEAT-013:
        // new registrations create verified accounts only after the mail link (RegisteredUserController::confirm),
        // and an email change in the profile keeps the old email verified until the new one is confirmed
        // (User::requestEmailChange). Someone with a pending registration has no account yet, so they are a guest.
        Blade::if('verified', fn () => (bool) auth()->user()?->hasVerifiedEmail());

        // the mail logo is attached to the mail, not loaded from the server (FEAT-016)
        Event::listen(MessageSending::class, [MailAssets::class, 'embedLogo']);

        // Old unverified accounts: their last link expired long ago, so every login (form or developer button) sends a
        // fresh one, at most once an hour (as long as a link is valid), and leads to the page that tells them so
        Event::listen(Login::class, function (Login $event) {
            if ($event->user->hasVerifiedEmail()) {
                return;
            }

            RateLimiter::attempt('verify-mail:' . $event->user->id, 1, fn () => $event->user->sendEmailVerificationNotification(), 3600);
            session()->put('url.intended', route('verification.notice'));
        });

        // Laravel's verify mail with a personal greeting
        VerifyEmail::toMailUsing(fn ($user, string $url) => (new MailMessage)
            ->subject(__('Verify Email Address'))
            ->greeting(__('Hello and welcome dear :name!', ['name' => $user->name]))
            ->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Email Address'), $url)
            ->line(__('If you did not create an account, no further action is required.')));

        // Named, so it has its own counter: a plain throttle:6,1 shares one counter per IP
        // with all other throttled routes, and the submit would use up the resend limit
        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(6)->by($request->ip()));
    }
}
