<?php

namespace App\Actions;

use Illuminate\Foundation\Http\FormRequest;
use Intervention\Image\Laravel\Facades\Image; // Import Image facade
use Illuminate\Support\Facades\Storage;

class StoreNameImage {

    public function handle(FormRequest $request): String {

        // Get the uploaded file
        $file = $request->file('info_file');
        
        // resize and format the image
        $resizedImage = Image::read($file)->scale(1400,1400)->toJpeg(quality: 100, progressive: true);

        // Generate a unique filename
        $extension = explode('/', $resizedImage->mimetype())[1];
        $filename = uniqid('resized_') . '_' . $file->getClientOriginalName() . '.' . $extension;
        
        
        // Store the resized image
        $filePath = 'files/posts/info-files/' . $filename;
        Storage::disk('public')->put($filePath, $resizedImage);

        return $filePath;
    }
}