<?php

namespace Tests\Feature\Posts;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSlugTest extends TestCase
{
    use RefreshDatabase;

    private function draft(string $title): Post
    {
        return Post::factory()->create(['title' => $title, 'is_published' => false, 'published_at' => null]);
    }

    public function test_slug_is_made_from_the_title_with_umlauts_transliterated(): void
    {
        $this->assertSame('schoener-sonnenuntergang-am-see', $this->draft('Schöner Sonnenuntergang am See!')->slug);
        $this->assertSame('strasse-im-uebergang', $this->draft('Straße im Übergang')->slug);
    }

    public function test_duplicates_get_a_numeric_suffix(): void
    {
        $this->assertSame('sonnenuntergang', $this->draft('Sonnenuntergang')->slug);
        $this->assertSame('sonnenuntergang-2', $this->draft('sonnenuntergang')->slug);
        $this->assertSame('sonnenuntergang-3', $this->draft('  SONNENUNTERGANG ')->slug);
    }

    public function test_title_without_letters_gets_a_random_slug(): void
    {
        $slug = $this->draft('🌅🌅 !!')->slug;

        $this->assertMatchesRegularExpression('/^[a-z0-9]{8}$/', $slug);
    }

    public function test_long_title_is_cut_at_a_word_boundary(): void
    {
        $slug = $this->draft(str_repeat('wonderful ', 12))->slug;

        $this->assertLessThanOrEqual(Post::SLUG_MAX_LENGTH, strlen($slug));
        $this->assertStringEndsWith('wonderful', $slug);
    }

    public function test_slug_follows_the_title_while_draft_and_is_fixed_after_publication(): void
    {
        $post = $this->draft('First idea');
        $post->update(['title' => 'Better idea']);
        $this->assertSame('better-idea', $post->slug);

        $post->update(['is_published' => true, 'published_at' => now()]);
        $post->update(['title' => 'Renamed after publishing']);
        $this->assertSame('better-idea', $post->fresh()->slug);

        // also when it was made a draft again in between
        $post->update(['is_published' => false]);
        $post->update(['title' => 'Again renamed']);
        $this->assertSame('better-idea', $post->fresh()->slug);
    }

    public function test_a_post_keeps_its_own_slug_when_saved_again(): void
    {
        $post = $this->draft('Same title');
        $post->update(['title' => 'Same title', 'is_sensitive' => true]);

        $this->assertSame('same-title', $post->fresh()->slug);
    }

    public function test_gallery_and_share_link_use_the_slug_anchor_and_no_id(): void
    {
        $post = Post::factory()->withImage()->create([
            'title' => 'Mondschein über Köln', 'is_published' => true, 'is_sensitive' => false, 'published_at' => now(),
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="post-mondschein-ueber-koeln"', false)
            ->assertSee('data-share-url="' . route('dashboard', ['page' => 1]) . '#post-mondschein-ueber-koeln"', false)
            ->assertDontSee('#post-' . $post->id, false)
            ->assertDontSee('id="post-' . $post->id . '"', false);
    }

    public function test_my_images_and_profile_link_to_the_slug_anchor(): void
    {
        $user = User::factory()->create();
        Post::factory()->forUser($user)->create([
            'title' => 'Blue door', 'is_published' => true, 'is_sensitive' => false, 'published_at' => now(),
        ]);

        $this->actingAs($user)->get(route('posts.index'))->assertSee('#post-blue-door', false);
        $this->actingAs($user)->get(route('profile.show', $user))->assertSee('#post-blue-door', false);
    }

    public function test_gallery_page_of_a_post_is_computed_from_its_position(): void
    {
        config(['app.posts_per_page' => 2]);
        $posts = collect(range(1, 3))->map(fn ($i) => Post::factory()->create([
            'is_published' => true, 'published_at' => now()->subDays($i),
        ]));

        // newest first: posts 1 and 2 on page 1, post 3 on page 2
        $this->assertSame(1, $posts[0]->galleryPage());
        $this->assertSame(1, $posts[1]->galleryPage());
        $this->assertSame(2, $posts[2]->galleryPage());
    }
}
