<?php

namespace App\Actions;

use Illuminate\Foundation\Http\FormRequest;
use Intervention\Image\Laravel\Facades\Image; // Import Image facade
use Illuminate\Support\Facades\Storage;

class StoreNameImage
{

    public function handle(FormRequest $request, $fileInputName, $directory, $maxSize = 1400, $quality = 100): String
    {
        // Get the uploaded file
        $file = $request->file($fileInputName); //e.g. 'image_file'

        // resize and format the image
        $resizedImage = Image::read($file)
            ->scale($maxSize, $maxSize)->toJpeg(quality: $quality, progressive: true);

        // Generate a unique filename
        $extension = explode('/', $resizedImage->mimetype())[1];
        $filename = uniqid('resized_' . $maxSize . '_') . '.' . $extension;


        // Store the resized image
        $filePath = $directory . $filename; //e.g. 'files/posts/images/'
        Storage::disk('public')->put($filePath, $resizedImage);

        return $filePath;
    }
}
