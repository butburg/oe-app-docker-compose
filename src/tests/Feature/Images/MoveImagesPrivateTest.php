<?php

namespace Tests\Feature\Images;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MoveImagesPrivateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('images');
    }

    public function test_files_are_moved_from_the_public_to_the_images_disk(): void
    {
        $variants = Post::factory()->withImage()->create()->image->variants;
        $variants->each(fn ($variant) => Storage::disk('public')->put($variant->path, 'jpeg-bytes'));

        $this->artisan('images:move-private')->assertSuccessful();

        foreach ($variants as $variant) {
            Storage::disk('public')->assertMissing($variant->path);
            $this->assertSame('jpeg-bytes', Storage::disk('images')->get($variant->path));
        }
    }

    public function test_dry_run_moves_nothing(): void
    {
        $variant = Post::factory()->withImage()->create()->image->variants->first();
        Storage::disk('public')->put($variant->path, 'jpeg-bytes');

        $this->artisan('images:move-private --dry-run')
            ->expectsOutputToContain('1 files to move')
            ->assertSuccessful();

        Storage::disk('public')->assertExists($variant->path);
        Storage::disk('images')->assertMissing($variant->path);
    }

    public function test_running_it_again_changes_nothing(): void
    {
        $variant = Post::factory()->withImage()->create()->image->variants->first();
        Storage::disk('images')->put($variant->path, 'jpeg-bytes');

        $this->artisan('images:move-private')
            ->expectsOutputToContain('0 files moved')
            ->assertSuccessful();

        $this->assertSame('jpeg-bytes', Storage::disk('images')->get($variant->path));
    }
}
