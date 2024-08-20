<?php

namespace App\Actions;

use App\Enums\ImageSizeType;
use App\Models\Image;
use App\Models\ImageVariant;

class CreateImageVariants
{
    public function handleVariant(Image $image, $file, array $sizeTypes, string $path = 'posts/images/'): void
    {
        foreach ($sizeTypes as $sizeType) {
            $sizeTypeEnum = ImageSizeType::from($sizeType);
            $filePath = "files/{$path}{$sizeType}/" . $image->id . ".jpeg";

            // Store the image and get the dimensions
            $imageData = app(StoreImage::class)->handleStore($file, $sizeTypeEnum, $filePath);

            // Check if the variant already exists
            $variant = $image->variants()->firstWhere('size_type', $sizeTypeEnum->value);

            if ($variant) {
                // Update the existing variant
                $variant->update([
                    'path' => $imageData['path'],
                    'quality' => $sizeTypeEnum->getQuality(),
                    'width' => $imageData['width'],
                    'height' => $imageData['height'],
                ]);
            } else {
                // Create a new variant if it doesn't exist
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
}
