<?php

namespace App\Actions;

use Intervention\Image\Laravel\Facades\Image; // Import Image facade
use Illuminate\Support\Facades\Storage;
use App\Enums\ImageSizeType;

class StoreImage
{
    public function handle($file, ImageSizeType $sizeType, string $filePath): array
    {
        // Load the image
        $image  = Image::read($file);

        // scale the image, only if its an avatar, make it scale and crop (cover) as a square
        // than set quality and make progressive, better for web loading
        if ($sizeType == ImageSizeType::EXTRA_SMALL or $sizeType == ImageSizeType::SMALL) {
            $image->cover(
                width: $sizeType->getMaxWidth(),
                height: $sizeType->getMaxHeight()
            );
        } else {
            $image->scale(
                width: $sizeType->getMaxWidth(),
                height: $sizeType->getMaxHeight()
            );
        }

        $encodedImage = $image->toJpeg(quality: $sizeType->getQuality(), progressive: true);
        Storage::disk('public')->put($filePath, $encodedImage);

        // reading the image width and height
        $width = $image->width();
        $height = $image->height();

        return [
            'path' => $filePath,
            'width' => $width,
            'height' => $height,
        ];
    }
}
