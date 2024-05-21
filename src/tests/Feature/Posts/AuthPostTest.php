<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthPostTest extends TestCase
{
    use RefreshDatabase;

       /**
     * Data provider for unauthenticated routes.
     *
     * @return array
     */
    public static function unauthenticatedRoutesProvider()
    {
        return [
            'posts' => ['posts'],
            'posts/create' => ['posts/create'],
            'posts/1/edit' => ['posts/1/edit'], // Assuming '1' is an example post ID
            'posts/1' => ['posts/1'], 
            'posts/1/make-draft' => ['posts/1/make-draft'],
            'posts/1/publish' => ['posts/1/publish'],
        ];
    }

    /**
     * Test unauthenticated user cannot access certain routes.
     *
     */
    #[DataProvider('unauthenticatedRoutesProvider')]
    public function test_unauthenticated_user_cannot_access_routes($route)
    {
        $response = $this->get($route);

        $response->assertStatus(302);
        $response->assertRedirect('login');
    }
}