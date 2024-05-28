<?php

namespace App\Actions;

use Illuminate\Foundation\Http\FormRequest;
use Intervention\Image\Laravel\Facades\Image; // Import Image facade
use Illuminate\Support\Facades\Storage;

class StoreNameImage
{

    public function handle(FormRequest $request): String
    {

        $image_size_max = 1400;

        // Get the uploaded file
        $file = $request->file('image_file');

        // resize and format the image
        $resizedImage = Image::read($file)->scale($image_size_max, $image_size_max)->toJpeg(quality: 100, progressive: true);

        // Generate a unique filename
        $extension = explode('/', $resizedImage->mimetype())[1];
        $filename = uniqid('resized_' . $image_size_max) . '.' . $extension;


        // Store the resized image
        $filePath = 'files/posts/images/' . $filename;
        Storage::disk('public')->put($filePath, $resizedImage);

        return $filePath;
    }
}
