<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnverifiedUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $author = User::factory()->create();
        $public = Post::factory()->forUser($author)->create([
            'title' => 'Open meadow',
            'is_published' => true,
            'is_sensitive' => false,
            'published_at' => now(),
        ]);
        Post::factory()->forUser($author)->create([
            'title' => 'Private fern',
            'is_published' => true,
            'is_sensitive' => true,
            'published_at' => now(),
        ]);
        Comment::create(['user_id' => $author->id, 'post_id' => $public->id, 'comment' => 'Lovely light here']);
    }

    public function test_guest_sees_no_protected_posts_and_no_comments(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Open meadow')
            ->assertDontSee('Private fern')
            ->assertDontSee('Lovely light here')
            ->assertSee('to view and post comments')
            ->assertDontSee('Verify your email');
    }

    public function test_unverified_user_sees_the_guest_view_with_a_verify_hint(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Open meadow')
            ->assertDontSee('Private fern')
            ->assertDontSee('Lovely light here')
            ->assertDontSee('Add comment...')
            ->assertSee('Verify your email')
            ->assertSee(route('verification.notice'))
            ->assertSee('Verify email')
            ->assertDontSee('My Images');

        $this->actingAs($user)->get(route('dashboard', ['q' => 'fern']))
            ->assertOk()
            ->assertDontSee('Private fern');
    }

    public function test_verified_user_sees_everything(): void
    {
        $this->actingAs(User::factory()->create())->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Open meadow')
            ->assertSee('Private fern')
            ->assertSee('Lovely light here')
            ->assertSee('Add comment...')
            ->assertSee('My Images')
            ->assertDontSee('Verify your email');
    }
}
