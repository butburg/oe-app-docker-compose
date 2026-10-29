<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
    }
}
