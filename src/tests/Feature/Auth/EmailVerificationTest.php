<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verify_page_looks_like_a_pending_registration_but_keeps_the_account(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Check your email')
            ->assertSee($user->email)
            ->assertSee(route('verification.email'))
            ->assertSee(route('logout'))
            ->assertDontSee(route('register.pending.destroy'))
            ->assertDontSee(route('profile.destroy'));
    }

    public function test_unverified_user_can_correct_the_email(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create(['email' => 'typo@example.com']);

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->patch(route('verification.email'), ['email' => 'Right@Example.com'])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'link-sent');

        $user->refresh();
        $this->assertSame('right@example.com', $user->email);
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_link_for_the_old_email_stops_working_after_the_correction(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'typo@example.com']);
        $oldLink = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1('typo@example.com')]);

        $this->actingAs($user)->patch(route('verification.email'), ['email' => 'right@example.com']);
        $this->actingAs($user->refresh())->get($oldLink)->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_of_another_account_cannot_be_taken(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->unverified()->create(['email' => 'typo@example.com']);

        $this->actingAs($user)
            ->patch(route('verification.email'), ['email' => 'taken@example.com'])
            ->assertSessionHasErrors('email');

        $this->assertSame('typo@example.com', $user->fresh()->email);
    }

    public function test_verified_user_cannot_change_the_email_this_way(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)->patch(route('verification.email'), ['email' => 'new@example.com']);

        $this->assertSame('old@example.com', $user->fresh()->email);
    }
}
