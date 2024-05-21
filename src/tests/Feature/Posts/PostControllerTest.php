<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Actions\StoreNameImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_posts()
    {
        $user = User::factory()->create();

        $draftPost = Post::factory()->create(['is_published' => false]);
        $publishedPost = Post::factory()->create(['is_published' => true]);

        $response = $this->actingAs($user)->get(route('posts.index'));

        $response->assertStatus(200);
        $response->assertViewHas('draftPosts');
        $response->assertViewHas('publishedPosts');
    }

    public function test_create_displays_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('posts.create'));

        $response->assertStatus(200);
        $response->assertViewIs('posts.form');
    }

    public function test_store_creates_post()
    {
        $user = User::factory()->create();

        Storage::fake('public');
        $file = UploadedFile::fake()->image('info_file.jpg');
        $data = [
            'title' => 'Test Post',
            'info_file' => $file,
        ];

        $response = $this->actingAs($user)->post(route('posts.store'), $data);

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', ['title' => 'Test Post']);
        
        Storage::disk('public')->assertExists('files/posts/info-files/' . $file->hashName());
    }

    public function test_show_displays_post()
    {
        $user = User::factory()->create();

        $post = Post::factory()->create();

        $response = $this->actingAs($user)->get(route('posts.show', $post));

        $response->assertStatus(200);
        $response->assertViewHas('post');
    }

    public function test_edit_displays_form()
    {
        $user = User::factory()->create();

        $post = Post::factory()->create();

        $response = $this->actingAs($user)->get(route('posts.edit', $post));

        $response->assertStatus(200);
        $response->assertViewHas('post');
    }

    public function test_update_updates_post()
    {
        $user = User::factory()->create();

        Storage::fake('public');
        $post = Post::factory()->create(['info_file' => 'old_image.jpg']);
        $file = UploadedFile::fake()->image('info_file.jpg');
        $data = [
            'title' => 'Updated Post',
            'info_file' => $file,
        ];

        $response = $this->actingAs($user)->put(route('posts.update', $post), $data);

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', ['title' => 'Updated Post']);
        Storage::disk('public')->assertExists('files/posts/info-files/' . $file->hashName());
        Storage::disk('public')->assertMissing('files/posts/info-files/old_image.jpg');
    }

    public function test_destroy_deletes_post()
    {
        $user = User::factory()->create();

        Storage::fake('public');
        $post = Post::factory()->create(['info_file' => 'image.jpg']);

        $response = $this->actingAs($user)->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        Storage::disk('public')->assertMissing('files/posts/info-files/image.jpg');
    }

    public function test_publish_publishes_post()
    {
        $user = User::factory()->create();

        $post = Post::factory()->create(['is_published' => false]);

        $response = $this->actingAs($user)->post(route('posts.publish', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_published' => true]);
    }

    public function test_makedraft_saves_post_as_draft()
    {
        $user = User::factory()->create();

        $post = Post::factory()->create(['is_published' => true]);

        $response = $this->actingAs($user)->post(route('posts.make-draft', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_published' => false]);
    }
}
