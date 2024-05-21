<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Actions\StoreNameImage;
use App\Http\Requests\Post\StoreRequest;
    
class StoreNameImageTest extends TestCase
{
    public function test_handle_saves_image_with_correct_name()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('info_file.jpg');

        $request = new StoreRequest([
            'info_file' => $file,
        ]);
        $request->setMethod('POST');

        $action = new StoreNameImage();
        $filePath = $action->handle($request);

        Storage::disk('public')->assertExists($filePath);
        $this->assertStringStartsWith('files/posts/info-files/resized_', $filePath);
    }
}
