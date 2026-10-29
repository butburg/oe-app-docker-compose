<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnverifiedAccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_page_offers_account_deletion(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('verification.notice'))
            ->assertOk()
            ->assertSee(route('verification.account.destroy'));
    }

    public function test_unverified_user_can_delete_themselves(): void
    {
        $user = User::factory()->unverified()->create();
        $bystander = User::factory()->unverified()->create();

        $response = $this->actingAs($user)
            ->delete(route('verification.account.destroy'), ['password' => 'password']);

        $response->assertRedirect('/');
        $response->assertSessionHas('notif.success');
        $this->assertGuest();
        $this->assertModelMissing($user);
        $this->assertModelExists($bystander);
    }

    public function test_wrong_password_deletes_nothing(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->delete(route('verification.account.destroy'), ['password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect(route('verification.notice'));

        $this->assertModelExists($user);
        $this->assertAuthenticatedAs($user);
    }

    public function test_verified_user_is_sent_to_the_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('verification.account.destroy'), ['password' => 'password'])
            ->assertRedirect(route('profile.edit'));

        $this->assertModelExists($user);
    }

    public function test_guest_cannot_use_the_route(): void
    {
        $user = User::factory()->unverified()->create();

        $this->delete(route('verification.account.destroy'), ['password' => 'password'])
            ->assertRedirect(route('login'));

        $this->assertModelExists($user);
    }
}
