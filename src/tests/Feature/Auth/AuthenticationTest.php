<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_authenticate_with_uppercase_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->post('/login', [
            'email' => ' Test@Example.COM ',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_unverified_user_gets_a_fresh_link_and_the_verify_page_on_login(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('verification.notice'));

        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
        Notification::assertSentTo($user, VerifyEmail::class,
            fn (VerifyEmail $mail) => $mail->toMail($user)->greeting === "Hello and welcome dear {$user->name}!");
    }

    public function test_unverified_user_gets_at_most_one_link_an_hour_on_login(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/logout');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('verification.notice'));
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);

        $this->post('/logout');
        $this->travel(61)->minutes();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        Notification::assertSentToTimes($user, VerifyEmail::class, 2);
    }
}
