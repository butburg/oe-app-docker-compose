<?php

namespace Tests\Feature;

use App\Mail\ConfirmNewEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailChangeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->user = User::factory()->create(['email' => 'old@example.com']);
    }

    private function requestChange(string $email = 'new@example.com'): void
    {
        $this->actingAs($this->user)
            ->patch(route('profile.updateEmail'), ['email' => $email])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));
    }

    private function linkSentTo(string $email): string
    {
        $mail = Mail::sent(ConfirmNewEmail::class, fn ($mail) => $mail->hasTo($email))->last();
        $this->assertNotNull($mail, "No confirmation mail sent to {$email}");

        return $mail->url;
    }

    public function test_change_keeps_the_old_email_active_and_verified(): void
    {
        $this->requestChange();

        $this->user->refresh();
        $this->assertSame('old@example.com', $this->user->email);
        $this->assertSame('new@example.com', $this->user->pending_email);
        $this->assertTrue($this->user->hasVerifiedEmail());
        Mail::assertSent(ConfirmNewEmail::class, fn ($mail) => $mail->hasTo('new@example.com'));

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Waiting for confirmation of')
            ->assertSee('new@example.com');
    }

    public function test_old_email_still_logs_in_while_the_change_is_pending(): void
    {
        $this->requestChange();
        $this->post('/logout');

        $this->post('/login', ['email' => 'new@example.com', 'password' => 'password']);
        $this->assertGuest();

        $this->post('/login', ['email' => 'old@example.com', 'password' => 'password']);
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_link_switches_to_the_new_email(): void
    {
        $this->requestChange();
        $link = $this->linkSentTo('new@example.com');

        // Opened on another device without being logged in
        $this->post('/logout');
        $this->get($link)->assertRedirect(route('login'))->assertSessionHas('status');

        $this->user->refresh();
        $this->assertSame('new@example.com', $this->user->email);
        $this->assertNull($this->user->pending_email);
        $this->assertTrue($this->user->hasVerifiedEmail());

        $this->post('/login', ['email' => 'new@example.com', 'password' => 'password']);
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_second_click_after_a_mail_scanner_says_the_email_is_changed(): void
    {
        $this->requestChange();
        $link = $this->linkSentTo('new@example.com');
        $this->post('/logout');

        // The scanner opens the link first, then the user clicks it
        $this->get($link);
        $this->get($link)
            ->assertRedirect(route('login'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Your email is already changed to new@example.com.');
    }

    public function test_cancel_keeps_the_old_email_and_kills_the_link(): void
    {
        $this->requestChange();
        $link = $this->linkSentTo('new@example.com');

        $this->delete(route('profile.email.cancel'))->assertRedirect(route('profile.edit'));
        $this->assertNull($this->user->refresh()->pending_email);

        $this->get($link)->assertSessionHasErrors('email');
        $this->assertSame('old@example.com', $this->user->refresh()->email);
    }

    public function test_correcting_a_typo_makes_the_first_link_invalid(): void
    {
        $this->requestChange('typo@exmaple.com');
        $typoLink = $this->linkSentTo('typo@exmaple.com');
        $this->requestChange('new@example.com');

        $this->get($typoLink)->assertSessionHasErrors('email');
        $this->assertSame('old@example.com', $this->user->refresh()->email);

        $this->get($this->linkSentTo('new@example.com'));
        $this->assertSame('new@example.com', $this->user->refresh()->email);
    }

    public function test_resend_sends_the_link_again(): void
    {
        $this->requestChange();

        $this->post(route('profile.email.resend'))->assertRedirect(route('profile.edit'));

        Mail::assertSent(ConfirmNewEmail::class, 2);
    }

    public function test_expired_or_tampered_link_changes_nothing(): void
    {
        $this->requestChange();
        $link = $this->linkSentTo('new@example.com');

        $this->get($link . 'x')->assertForbidden();

        $this->travel(User::EMAIL_CHANGE_VALID_MINUTES + 1)->minutes();
        $this->get($link)->assertForbidden();

        $this->assertSame('old@example.com', $this->user->refresh()->email);
    }

    public function test_link_fails_if_the_email_was_taken_in_between(): void
    {
        $this->requestChange();
        $link = $this->linkSentTo('new@example.com');
        User::factory()->create(['email' => 'new@example.com']);

        $this->get($link)->assertSessionHasErrors('email');

        $this->user->refresh();
        $this->assertSame('old@example.com', $this->user->email);
        $this->assertNull($this->user->pending_email);
    }

    public function test_email_of_another_account_is_rejected(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->user)
            ->patch(route('profile.updateEmail'), ['email' => 'taken@example.com'])
            ->assertSessionHasErrors('email');

        $this->assertNull($this->user->refresh()->pending_email);
        Mail::assertNothingSent();
    }
}
