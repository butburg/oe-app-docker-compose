<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GenerateImpressumImageTest extends TestCase
{
    private string $publicPath;

    protected function setUp(): void
    {
        parent::setUp();

        // never overwrite the real images in public/
        $this->publicPath = sys_get_temp_dir() . '/impressum-image-test';
        File::ensureDirectoryExists($this->publicPath . '/images');
        $this->app->usePublicPath($this->publicPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->publicPath);

        parent::tearDown();
    }

    public function test_it_writes_the_contact_image_with_one_line_per_argument(): void
    {
        $this->artisan('impressum:image', ['--contact' => true, 'lines' => ['Name', 'Street 1', '12345 City']])
            ->assertSuccessful();

        [$width, $height] = getimagesize($this->publicPath . '/images/impressum-contact-protected.png');
        $this->assertGreaterThan($width, $height * 3);
        $this->assertFileDoesNotExist($this->publicPath . '/images/impressum-mail-protected.png');
    }

    public function test_it_writes_the_mail_image(): void
    {
        $this->artisan('impressum:image', ['--mail' => true, 'lines' => ['someone@example.com']])
            ->assertSuccessful();

        $this->assertFileExists($this->publicPath . '/images/impressum-mail-protected.png');
    }

    public function test_it_requires_exactly_one_type_flag(): void
    {
        $this->artisan('impressum:image', ['lines' => ['x']])->assertFailed();
        $this->artisan('impressum:image', ['--contact' => true, '--mail' => true, 'lines' => ['x']])->assertFailed();
    }
}
