<?php

namespace Tests\Feature\Images;

use App\Models\Image;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageLinkTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('images');
        $this->owner = User::factory()->create();
        $this->post = Post::factory()->forUser($this->owner)->withImage()->create([
            'is_published' => true,
            'is_sensitive' => false,
            'published_at' => now(),
        ]);
        $this->storeFiles($this->post->image);
    }

    // The factory only creates the DB rows, the controller needs the files to send them
    private function storeFiles(Image $image): void
    {
        $image->variants->each(fn ($variant) => Storage::disk('images')->put($variant->path, 'jpeg-bytes'));
    }

    private function image(): Image
    {
        return $this->post->fresh()->image;
    }

    private function path(string $url): string
    {
        return parse_url($url, PHP_URL_PATH);
    }

    public function test_valid_link_sends_the_file(): void
    {
        $response = $this->get($this->path($this->image()->url('m')));

        $response->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertHeaderMissing('Set-Cookie');
        $this->assertSame('jpeg-bytes', file_get_contents($response->baseResponse->getFile()->getPathname()));
    }

    public function test_cache_lifetime_ends_when_the_link_expires(): void
    {
        $response = $this->get($this->path($this->image()->url('s')));

        preg_match('/max-age=(\d+)/', $response->headers->get('Cache-Control'), $match);
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertGreaterThan(Image::windowSeconds(), (int) $match[1]);
        $this->assertLessThanOrEqual(2 * Image::windowSeconds(), (int) $match[1]);
    }

    public function test_link_of_the_previous_window_still_works(): void
    {
        $url = $this->path($this->image()->url('s'));

        $this->travel(config('images.link_window_days'))->days();

        $this->get($url)->assertOk();
    }

    public function test_link_two_windows_old_is_not_found(): void
    {
        $url = $this->path($this->image()->url('s'));

        $this->travel(2 * config('images.link_window_days'))->days();

        $this->get($url)->assertNotFound();
        // a fresh page render gives a working link again
        $this->get($this->path($this->image()->url('s')))->assertOk();
    }

    public function test_wrong_token_and_unknown_variant_are_not_found(): void
    {
        $variant = $this->image()->variants->first();

        $this->get("/img/{$variant->public_id}/" . str_repeat('a', 32))->assertNotFound();
        $this->get('/img/' . str_repeat('0', 16) . '/' . Image::token(999999, 0, Image::currentWindow()))->assertNotFound();
        // the counting id does not work in the link
        $this->get("/img/{$variant->id}/" . Image::token($variant->id, 0, Image::currentWindow()))->assertNotFound();
        // a valid token of another variant does not open this one
        $other = $this->image()->variants->last();
        $this->get("/img/{$variant->public_id}/" . Image::token($other->id, 0, Image::currentWindow()))->assertNotFound();
    }

    public function test_making_the_post_sensitive_invalidates_old_links(): void
    {
        $url = $this->path($this->image()->url('s'));

        $this->actingAs($this->owner)->get(route('posts.toggleSensitive', $this->post->id));

        $this->get($url)->assertNotFound();
        $this->get($this->path($this->image()->url('s')))->assertOk();
    }

    public function test_making_the_post_a_draft_invalidates_old_links(): void
    {
        $url = $this->path($this->image()->url('s'));
        $admin = User::factory()->create(['usertype' => 'admin']);

        $this->actingAs($admin)->get(route('posts.make-draft', $this->post->id));

        $this->get($url)->assertNotFound();
    }

    public function test_replacing_the_image_invalidates_old_links(): void
    {
        $draft = Post::factory()->forUser($this->owner)->withImage()->create(['is_published' => false, 'is_sensitive' => false]);
        $this->storeFiles($draft->image);
        $url = $this->path($draft->image->url('s'));
        $this->get($url)->assertOk();

        $this->actingAs($this->owner)->put(route('posts.update', $draft->id), [
            'title' => 'New image', 'image_file' => UploadedFile::fake()->image('b.jpg', 120, 120),
        ])->assertRedirect(route('posts.index', ['tab' => 'drafts']));

        $this->get($url)->assertNotFound();
        $this->get($this->path($draft->fresh()->image->url('s')))->assertOk();
    }

    public function test_link_has_a_random_id_and_not_the_counting_id(): void
    {
        $variant = $this->image()->variants->firstWhere('size_type', 's');

        $this->assertMatchesRegularExpression('#^/img/[0-9a-f]{16}/[0-9a-f]{32}$#', $this->path($this->image()->url('s')));
        $this->assertStringStartsWith("/img/{$variant->public_id}/", $this->path($this->image()->url('s')));
    }

    public function test_replacing_the_profile_image_invalidates_old_links(): void
    {
        $this->actingAs($this->owner)->patch(route('profile.updateImage'), [
            'profile_image' => UploadedFile::fake()->image('a.jpg', 120, 120),
        ]);
        $url = $this->path($this->owner->fresh()->image->url('s'));
        $this->get($url)->assertOk();

        // fresh user: the first request cached "no profile image" on $this->owner
        $this->actingAs($this->owner->fresh())->patch(route('profile.updateImage'), [
            'profile_image' => UploadedFile::fake()->image('b.jpg', 120, 120),
        ]);

        $this->get($url)->assertNotFound();
        $this->get($this->path($this->owner->fresh()->image->url('s')))->assertOk();
    }

    public function test_deleted_post_links_are_not_found(): void
    {
        $url = $this->path($this->image()->url('s'));

        $this->actingAs($this->owner)->delete(route('posts.destroy', $this->post->id));

        $this->get($url)->assertNotFound();
    }

    public function test_gallery_shows_image_links_and_no_storage_paths(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('/img/', false)
            ->assertDontSee('/storage/files/', false);
    }
}
