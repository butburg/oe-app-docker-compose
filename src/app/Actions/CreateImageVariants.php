<?php

namespace App\Actions;

use App\Models\Image;
use App\Models\ImageVariant;
use App\Enums\ImageSizeType;

class CreateImageVariants
{
    public function handle(Image $image, $file, array $sizeTypes): void
    {
        foreach ($sizeTypes as $sizeType) {
            $sizeTypeEnum = ImageSizeType::from($sizeType);
            $filePath = "files/posts/images/{$sizeType}/" . $image->id . ".jpeg";

            // Store the image and get the dimensions
            $imageData = app(StoreImage::class)->handle($file, $sizeTypeEnum, $filePath);

            ImageVariant::create([
                'image_id' => $image->id,
                'path' => $imageData['path'],
                'size_type' => $sizeTypeEnum->value,
                'quality' => $sizeTypeEnum->getQuality(),
                'width' => $imageData['width'],
                'height' => $imageData['height'],
            ]);
        }
    }
}
