<?php

namespace Tests\Feature\Auth;

use App\Mail\AccountAlreadyExists;
use App\Mail\ConfirmRegistration;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    private function register(array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->post('/register', $data + [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'privacy_policy' => 'on',
        ]);
    }

    /**
     * The link from the last confirmation mail sent to the address.
     */
    private function linkSentTo(string $email): string
    {
        $mail = Mail::sent(ConfirmRegistration::class, fn ($mail) => $mail->hasTo($email))->last();
        $this->assertNotNull($mail, "No confirmation mail sent to {$email}");

        return $mail->url;
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_submit_creates_no_user_and_sends_one_mail(): void
    {
        $response = $this->register();

        $response->assertRedirect(route('register.pending'));
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('pending_registrations', 1);
        Mail::assertSent(ConfirmRegistration::class, 1);

        $pending = PendingRegistration::first();
        $this->assertNotSame('password', $pending->password);
        $this->assertSame(64, strlen($pending->token));
        $this->assertStringNotContainsString($pending->token, $this->linkSentTo('test@example.com'));

        $this->get(route('register.pending'))
            ->assertOk()
            ->assertSee('test@example.com')
            ->assertDontSee(route('login'))
            ->assertDontSee(route('posts.create'));
    }

    public function test_email_is_lowercased_instead_of_rejected(): void
    {
        $this->register(['email' => '  Test@Example.COM '])->assertRedirect(route('register.pending'));
        $this->assertDatabaseHas('pending_registrations', ['email' => 'test@example.com']);

        $this->patch(route('register.pending.email'), ['email' => 'Other@Example.com'])
            ->assertSessionHas('status', 'link-sent');
        $this->assertDatabaseHas('pending_registrations', ['email' => 'other@example.com']);
    }

    public function test_pending_page_without_registration_redirects_to_register(): void
    {
        $this->get(route('register.pending'))->assertRedirect(route('register'));
    }

    public function test_actions_after_expired_session_lead_back_to_register(): void
    {
        // A fresh session has no pending registration, just like one that expired
        $this->post(route('register.pending.resend'))
            ->assertRedirect(route('register'))
            ->assertSessionHas('status', 'Your registration session has expired. Please register again.');
        Mail::assertNothingSent();
    }

    public function test_expired_csrf_token_redirects_back_instead_of_419(): void
    {
        // CSRF is not checked in tests, so a route throws what the check would throw
        \Illuminate\Support\Facades\Route::post('/_test/expired', function () {
            throw new \Illuminate\Session\TokenMismatchException();
        })->middleware('web');

        $this->from(route('register.pending'))
            ->post('/_test/expired', ['email' => 'new@example.com', 'password' => 'secret'])
            ->assertRedirect(route('register.pending'))
            ->assertSessionHas('status', 'Your session has expired. Please try again.')
            ->assertSessionHasInput('email', 'new@example.com')
            ->assertSessionMissing('_old_input.password');

        // Requests that expect JSON keep the plain 419
        $this->postJson('/_test/expired')->assertStatus(419);
    }

    public function test_link_creates_verified_user_and_logs_in(): void
    {
        $this->register();
        $link = $this->linkSentTo('test@example.com');

        // A fresh session, like a new tab or another device
        $this->flushSession();
        // Opening the link only shows the button, like a mail scanner would see it
        $this->get($link)->assertOk()->assertSee('Hi Test User')->assertSee('Activate my account');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();

        $response = $this->post($link);

        $response->assertRedirect(route('dashboard', absolute: false));
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertSame('Test User', $user->name);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('pending_registrations', 0);

        // The password from the form works
        $this->post('/logout');
        $this->post('/login', ['email' => 'test@example.com', 'password' => 'password']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_used_link_does_not_work_again(): void
    {
        $this->register();
        $link = $this->linkSentTo('test@example.com');
        $this->post($link);
        $this->post('/logout');

        $this->get($link)->assertOk()->assertSee('This link does not work');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_expired_link_does_not_work(): void
    {
        $this->register();
        $link = $this->linkSentTo('test@example.com');

        $this->travel(PendingRegistration::VALID_MINUTES + 1)->minutes();

        $this->get($link)->assertOk()->assertSee('This link does not work');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_expired_registrations_are_deleted_on_the_next_registration(): void
    {
        $this->register();
        $this->travel(PendingRegistration::VALID_MINUTES + 1)->minutes();
        $this->register(['email' => 'fresh@example.com', 'name' => 'Fresh']);

        $this->assertSame(['fresh@example.com'], PendingRegistration::pluck('email')->all());
    }

    public function test_aborted_registration_link_does_not_work(): void
    {
        $this->register();
        $link = $this->linkSentTo('test@example.com');

        $this->delete(route('register.pending.destroy'))->assertRedirect(route('register'));

        $this->assertDatabaseCount('pending_registrations', 0);
        $this->get($link)->assertOk()->assertSee('This link does not work');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_change_email_invalidates_old_link(): void
    {
        $this->register(['email' => 'typo@exmaple.com']);
        $oldLink = $this->linkSentTo('typo@exmaple.com');

        $this->patch(route('register.pending.email'), ['email' => 'test@example.com'])
            ->assertSessionHas('status', 'link-sent');

        $this->get($oldLink)->assertSee('This link does not work');

        $this->post($this->linkSentTo('test@example.com'))->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'typo@exmaple.com']);
    }

    public function test_resend_sends_new_link_and_is_throttled(): void
    {
        $this->register();

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('register.pending.resend'))->assertSessionHas('status', 'link-sent');
        }
        $this->post(route('register.pending.resend'))->assertStatus(429);

        Mail::assertSent(ConfirmRegistration::class, 4);
    }

    public function test_register_is_throttled_per_ip(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->register(['email' => "test{$i}@example.com"])->assertRedirect(route('register.pending'));
        }

        $this->register(['email' => 'test6@example.com'])->assertStatus(429);
    }

    public function test_mails_per_address_are_limited_and_keep_the_pending_link(): void
    {
        for ($i = 0; $i < PendingRegistration::MAILS_PER_HOUR; $i++) {
            // A new IP each time, so only the limit per address applies
            $this->withServerVariables(['REMOTE_ADDR' => "10.0.0.{$i}"])->register();
        }
        $link = $this->linkSentTo('test@example.com');

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.1.1'])->register(['name' => 'Attacker'])
            ->assertSessionHasErrors('email');
        $this->post(route('register.pending.resend'))->assertSessionHasErrors('email');

        Mail::assertSent(ConfirmRegistration::class, PendingRegistration::MAILS_PER_HOUR);
        $this->post($link)->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'name' => 'Test User']);
    }

    public function test_second_registration_for_same_email_replaces_the_first(): void
    {
        $this->register();
        $firstLink = $this->linkSentTo('test@example.com');

        $this->register(['name' => 'Other Name']);

        $this->assertDatabaseCount('pending_registrations', 1);
        $this->get($firstLink)->assertSee('This link does not work');
        $this->post($this->linkSentTo('test@example.com'));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'name' => 'Other Name']);
    }

    public function test_existing_email_is_not_revealed(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->register();

        $response->assertRedirect(route('register.pending'));
        $response->assertSessionHasNoErrors();
        Mail::assertNotSent(ConfirmRegistration::class);
        Mail::assertSent(AccountAlreadyExists::class, fn ($mail) => $mail->hasTo('test@example.com'));
    }

    public function test_link_fails_if_email_was_taken_in_between(): void
    {
        $this->register();
        $link = $this->linkSentTo('test@example.com');

        User::factory()->create(['email' => 'test@example.com']);

        $this->post($link)->assertOk()->assertSee('already taken');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }
}
