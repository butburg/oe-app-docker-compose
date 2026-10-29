<?php

namespace Tests\Feature;

use App\Actions\DeleteUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_name_can_be_updated(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.updateName'), [
                'name' => 'Test User',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('Old Name', $user->previous_name);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->from('/profile')
            ->patch(route('profile.updateEmail'), [
                'email' => $user->email,
            ])
            ->assertSessionHasErrors('email')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_failed_deletion_keeps_the_account_and_says_so_on_the_login_page(): void
    {
        $user = User::factory()->create();
        // Let the deletion itself fail, e.g. a database error inside the transaction
        $this->mock(DeleteUser::class)->shouldReceive('handle')->andThrow(new \RuntimeException('database gone'));

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your account could not be deleted. Please log in and try again.');

        // Logged out before the deletion, but the account is still there
        $this->assertGuest();
        $this->assertModelExists($user);
        // The message really shows up on the login page, not only in the session
        $this->get(route('login'))->assertSee('Your account could not be deleted');
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
