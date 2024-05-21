<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Actions\StoreNameImage;
use App\Http\Requests\Post\StoreRequest;
use Intervention\Image\Laravel\Facades\Image;  // Make sure to import the Image facade
    
class StoreNameImageTest extends TestCase
{
    public function test_handle_saves_image_with_correct_name()
    {
        // Create a real image file using Intervention Image
        $image = Image::create(100, 100)->fill('#ff0000'); // Create a 100x100 red image
        $path = storage_path('app/public/test_image.png');
        $image->save($path);

        // Create an UploadedFile instance from the saved image
        $file = new UploadedFile($path, 'test_image.png', 'image/png', null, true);

        // Mock the request
        $request = StoreRequest::create('/store', 'POST', [], [], ['info_file' => $file]);

        $action = new StoreNameImage();
        $filePath = $action->handle($request);

        // Assert that the file was stored correctly
        Storage::disk('public')->assertExists($filePath);

        // Cleanup: remove the temporary image file
        unlink($path);
        $this->assertStringStartsWith('files/posts/info-files/resized_', $filePath);
    }
}
