<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

private User $user;

    protected function setUp(): void{
        parent::setUp();

        $this->user = User::factory()->create();
    }



    public function test_index_displays_posts()
    {

        $draftPost = Post::factory()->create(['is_published' => false]);
        $publishedPost = Post::factory()->create(['is_published' => true]);

        $response = $this->actingAs($this->user)->get(route('posts.index'));

        $response->assertStatus(200);
        $response->assertViewHas('draftPosts');
        $response->assertViewHas('publishedPosts');
    }

    public function test_gallery_search_matches_title_and_author_without_exposing_sensitive_posts_to_guests(): void
    {
        Post::factory()->create([
            'title' => 'Fern in sunlight',
            'username' => 'Maya Chen',
            'is_published' => true,
            'is_sensitive' => false,
            'published_at' => now(),
        ]);
        Post::factory()->create([
            'title' => 'Quiet garden',
            'username' => 'Maya Chen',
            'is_published' => true,
            'is_sensitive' => false,
            'published_at' => now(),
        ]);
        Post::factory()->create([
            'title' => 'Private fern',
            'username' => 'Maya Chen',
            'is_published' => true,
            'is_sensitive' => true,
            'published_at' => now(),
        ]);

        $this->get(route('dashboard', ['q' => 'fern']))
            ->assertOk()
            ->assertSee('Fern in sunlight')
            ->assertDontSee('Private fern');

        $this->get(route('dashboard', ['q' => 'Maya']))
            ->assertOk()
            ->assertSee('Fern in sunlight')
            ->assertSee('Quiet garden')
            ->assertDontSee('Private fern');

        $this->actingAs($this->user)
            ->get(route('dashboard', ['q' => 'Private']))
            ->assertOk()
            ->assertSee('Private fern');
    }

    public function test_gallery_search_ignores_case_and_matches_umlauts(): void
    {
        Post::factory()->create([
            'title' => 'Blühende Wiese',
            'username' => 'Jörg Müller',
            'is_published' => true,
            'is_sensitive' => false,
            'published_at' => now(),
        ]);

        $this->get(route('dashboard', ['q' => 'WIESE']))
            ->assertOk()
            ->assertSee('Blühende Wiese');

        $this->get(route('dashboard', ['q' => 'blühende']))
            ->assertOk()
            ->assertSee('Blühende Wiese');

        $this->get(route('dashboard', ['q' => 'Müller']))
            ->assertOk()
            ->assertSee('Blühende Wiese');
    }

    public function test_create_displays_form()
    {

        $response = $this->actingAs($this->user)->get(route('posts.create'));

        $response->assertStatus(200);
        $response->assertViewIs('posts.edit');
    }

    public function test_store_creates_post()
    {

        Storage::fake('images');
        $file = UploadedFile::fake()->image('image_file.jpg', 1200, 600);
        $data = [
            'title' => 'Test Post',
            'image_file' => $file,
        ];

        $response = $this->actingAs($this->user)->post(route('posts.store'), $data);

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', ['title' => 'Test Post']);

        $post = Post::where('title', 'Test Post')->firstOrFail();
        $image = $post->image;
        $smallVariantPath = "files/posts/images/s/{$image->id}.jpeg";

        $this->assertDatabaseHas('image_variants', [
            'image_id' => $image->id,
            'size_type' => 's',
            'path' => $smallVariantPath,
        ]);
        Storage::disk('images')->assertExists($smallVariantPath);
        $dimensions = getimagesize(Storage::disk('images')->path($smallVariantPath));
        $this->assertSame([100, 100], [$dimensions[0], $dimensions[1]]);
    }

    public function test_edit_displays_form()
    {

        $post = Post::factory()->forUser($this->user)->create(['is_published' => false]);

        $response = $this->actingAs($this->user)->get(route('posts.edit', $post));

        $response->assertStatus(200);
        $response->assertViewHas('post');
    }

    public function test_edit_of_published_post_redirects_owner_back()
    {
        $post = Post::factory()->forUser($this->user)->create(['is_published' => true, 'published_at' => now()]);

        $response = $this->actingAs($this->user)->get(route('posts.edit', $post));

        $response->assertRedirect(route('posts.index'));
    }

    public function test_edit_of_foreign_post_is_forbidden()
    {
        $post = Post::factory()->create(['is_published' => false]);

        $this->actingAs($this->user)->get(route('posts.edit', $post))->assertForbidden();
    }

    public function test_update_updates_post()
    {

        Storage::fake('images');
        $post = Post::factory()->forUser($this->user)->withImage()->create(['is_published' => false]);
        $file = UploadedFile::fake()->image('image_file.jpg', 1200, 600);
        $data = [
            'title' => 'Updated Post',
            'image_file' => $file,
        ];

        $response = $this->actingAs($this->user)->put(route('posts.update', $post), $data);

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Updated Post']);
        $image = $post->image()->first();
        Storage::disk('images')->assertExists("files/posts/images/s/{$image->id}.jpeg");
    }

    public function test_destroy_deletes_post()
    {

        Storage::fake('images');
        $post = Post::factory()->forUser($this->user)->withImage()->create();
        $image = $post->image()->first();
        foreach ($image->variants as $variant) {
            Storage::disk('images')->put($variant->path, 'image');
        }

        $response = $this->actingAs($this->user)->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('images', ['id' => $image->id]);
        foreach ($image->variants as $variant) {
            Storage::disk('images')->assertMissing($variant->path);
        }
    }

    public function test_publish_publishes_post()
    {

        $post = Post::factory()->forUser($this->user)->create(['is_published' => false]);

        $response = $this->actingAs($this->user)->get(route('posts.publish', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_published' => true]);
    }

    public function test_makedraft_saves_post_as_draft()
    {

        $admin = User::factory()->create(['usertype' => 'admin']);
        $post = Post::factory()->create(['is_published' => true]);

        $response = $this->actingAs($admin)->get(route('posts.make-draft', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_published' => false]);
    }

    public function test_makedraft_is_admin_only()
    {
        $post = Post::factory()->forUser($this->user)->create(['is_published' => true]);

        $this->actingAs($this->user)->get(route('posts.make-draft', $post))->assertRedirect('/');
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_published' => true]);
    }
}
