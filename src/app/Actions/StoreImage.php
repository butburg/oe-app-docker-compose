<?php

namespace App\Actions;

use Intervention\Image\Laravel\Facades\Image; // Import Image facade
use Illuminate\Support\Facades\Storage;
use App\Enums\ImageSizeType;

class StoreImage
{
    public function handleStore($file, ImageSizeType $sizeType, string $filePath): array
    {
        // Load the image
        $image  = Image::read($file);

        // reading the image width and height
        $width = $image->width();
        $height = $image->height();

        // scale the image, only if its an avatar, make it scale and crop (cover) as a square
        // than set quality and make progressive, better for web loading
        if ($sizeType == ImageSizeType::EXTRA_SMALL or $sizeType == ImageSizeType::SMALL) {
            $image->coverDown(
                width: $sizeType->getMaxWidth(),
                height: $sizeType->getMaxHeight()
            );
        } else {
            $image->scaleDown(
                width: $sizeType->getMaxWidth(),
                height: $sizeType->getMaxHeight()
            );
        }

        $encodedImage = $image->toJpeg(quality: $sizeType->getQuality(), progressive: true);
        Storage::disk('public')->put($filePath, $encodedImage);

        return [
            'path' => $filePath,
            'width' => $width,
            'height' => $height,
        ];
    }
}
