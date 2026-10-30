<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // the developer login buttons only exist on a developer machine
        $this->app['env'] = 'local';
    }

    /**
     * Clicks "Developer Login (Unverified)" on the login page.
     */
    private function loginAsUnverified(): User
    {
        $html = $this->get(route('login'))->assertOk()->getContent();
        preg_match_all('/<form method="POST" action="[^"]*laravel-login-link-login">(.*?)<\/form>/s', $html, $forms);
        $form = collect($forms[1])->first(fn ($form) => str_contains($form, 'Developer Login (Unverified)'));
        preg_match_all('/name="([^"]+)" value="([^"]*)"/', $form, $fields, PREG_SET_ORDER);
        $data = collect($fields)->mapWithKeys(fn ($field) => [$field[1] => html_entity_decode($field[2])])->all();

        $this->post(route('loginLinkLogin'), $data)->assertRedirect();

        return auth()->user();
    }

    public function test_uses_the_seeded_unverified_user_first(): void
    {
        User::factory()->create(['email' => 'verified@user.com']);
        User::factory()->unverified()->create(['email' => 'other-unverified@user.com']);
        $seeded = User::factory()->unverified()->create(['email' => 'user-unverified@user.com']);

        $this->assertTrue($this->loginAsUnverified()->is($seeded));
    }

    public function test_uses_another_unverified_user_when_the_seeded_one_was_changed(): void
    {
        // like a dev DB where the seeded account got verified and a new email
        User::factory()->create(['email' => 'user-unverified@user.com']);
        $other = User::factory()->unverified()->create(['email' => 'someone@user.com']);

        $this->assertTrue($this->loginAsUnverified()->is($other));
    }

    public function test_creates_an_unverified_user_when_there_is_none(): void
    {
        User::factory()->create(['email' => 'user-unverified@user.com']);

        $user = $this->loginAsUnverified();

        $this->assertNull($user->email_verified_at);
        $this->assertNotSame('user-unverified@user.com', $user->email);
        $this->assertSame(2, User::count());
    }
}
