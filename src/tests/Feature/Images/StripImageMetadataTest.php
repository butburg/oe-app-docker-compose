<?php

namespace Tests\Feature\Images;

use App\Models\Image;
use App\Models\ImageVariant;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StripImageMetadataTest extends TestCase
{
    use RefreshDatabase;

    private const ICC = 'test-icc-profile-bytes';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /**
     * A 40x20 JPEG like from a phone: EXIF with camera make, orientation 6 (rotate 90°) and a GPS position,
     * plus an ICC colour profile. Built in code, so no binary fixture is needed.
     */
    private function photoWithGps(): string
    {
        $entry = fn (int $tag, int $type, int $count, string $value) => pack('vvV', $tag, $type, $count) . $value;
        $tiff = "II*\0" . pack('V', 8)
            . pack('v', 3)
            . $entry(0x010F, 2, 8, pack('V', 50))        // Make, string at offset 50
            . $entry(0x0112, 3, 1, pack('vv', 6, 0))     // Orientation
            . $entry(0x8825, 4, 1, pack('V', 58))        // GPS IFD at offset 58
            . pack('V', 0)
            . "TestCam\0"
            . pack('v', 4)
            . $entry(0x0001, 2, 2, "N\0\0\0")
            . $entry(0x0002, 5, 3, pack('V', 112))       // latitude rationals at 112
            . $entry(0x0003, 2, 2, "E\0\0\0")
            . $entry(0x0004, 5, 3, pack('V', 136))       // longitude rationals at 136
            . pack('V', 0)
            . pack('V6', 52, 1, 31, 1, 0, 1)
            . pack('V6', 13, 1, 24, 1, 0, 1);

        $imagick = new \Imagick();
        $imagick->newImage(40, 20, 'red');
        $imagick->setImageFormat('jpeg');
        $imagick->setImageOrientation(\Imagick::ORIENTATION_RIGHTTOP);
        $imagick->setImageProfile('exif', "Exif\0\0" . $tiff);
        $imagick->setImageProfile('icc', self::ICC);

        $path = tempnam(sys_get_temp_dir(), 'gps') . '.jpg';
        file_put_contents($path, $imagick->getImageBlob());

        return $path;
    }

    private function stored(string $path): \Imagick
    {
        return new \Imagick(Storage::disk('public')->path($path));
    }

    public function test_fixture_contains_gps(): void
    {
        $imagick = new \Imagick($this->photoWithGps());

        $this->assertSame('N', $imagick->getImageProperty('exif:GPSLatitudeRef'));
        $this->assertSame(6, $imagick->getImageOrientation());
    }

    public function test_uploaded_post_image_variants_contain_no_exif_and_keep_orientation_and_colour_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('posts.store'), [
            'title' => 'Holiday',
            'image_file' => new UploadedFile($this->photoWithGps(), 'holiday.jpg', 'image/jpeg', null, true),
        ])->assertRedirect(route('posts.index'));

        $variants = Post::firstOrFail()->image->variants;
        $this->assertCount(4, $variants);

        foreach ($variants as $variant) {
            $imagick = $this->stored($variant->path);
            $this->assertSame([], $imagick->getImageProperties('exif:*'), $variant->size_type);
            $this->assertSame(['icc'], $imagick->getImageProfiles('*', false), $variant->size_type);
            $this->assertSame(self::ICC, $imagick->getImageProfile('icc'));
        }

        // orientation 6 was applied to the pixels: the wide photo is stored as portrait
        $this->assertLessThan($this->stored($variants->firstWhere('size_type', 'xl')->path)->getImageHeight(),
            $this->stored($variants->firstWhere('size_type', 'xl')->path)->getImageWidth());
    }

    public function test_profile_image_contains_no_exif(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.updateImage'), [
            'profile_image' => new UploadedFile($this->photoWithGps(), 'me.jpg', 'image/jpeg', null, true),
        ]);

        $this->assertNotEmpty($user->fresh()->image->variants);
        foreach ($user->fresh()->image->variants as $variant) {
            $this->assertSame([], $this->stored($variant->path)->getImageProperties('exif:*'));
        }
    }

    public function test_command_strips_existing_files_and_is_idempotent(): void
    {
        $image = Image::create(['upload_size' => 1]);
        $variant = ImageVariant::create([
            'image_id' => $image->id, 'path' => 'files/posts/images/l/1.jpeg', 'size_type' => 'l', 'quality' => 80, 'width' => 40, 'height' => 20,
        ]);
        Storage::disk('public')->put($variant->path, file_get_contents($this->photoWithGps()));

        $this->artisan('images:strip-metadata --dry-run')
            ->expectsOutputToContain('Would strip: files/posts/images/l/1.jpeg')
            ->assertSuccessful();
        $this->assertSame('N', $this->stored($variant->path)->getImageProperty('exif:GPSLatitudeRef'));

        $this->artisan('images:strip-metadata')->expectsOutputToContain('1 files stripped')->assertSuccessful();
        $this->assertSame([], $this->stored($variant->path)->getImageProperties('exif:*'));
        $this->assertSame(self::ICC, $this->stored($variant->path)->getImageProfile('icc'));

        $this->artisan('images:strip-metadata')->expectsOutputToContain('0 files stripped')->assertSuccessful();
    }
}
