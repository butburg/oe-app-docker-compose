<?php

namespace App\Actions;

use Intervention\Image\Laravel\Facades\Image; // Import Image facade
use Illuminate\Support\Facades\Storage;
use App\Enums\ImageSizeType;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Finder\SplFileInfo;
use InvalidArgumentException;

class StoreImage
{
    public function handleStore($file, ImageSizeType $sizeType, string $filePath): array
    {
        //TODO check if here SplFileInfo is still needed for likle SVG file or so, maybe UploadedFile can be enough
        // Check if the file is an instance of either UploadedFile or SplFileInfo
        if (!($file instanceof UploadedFile || $file instanceof SplFileInfo)) {
            throw new InvalidArgumentException('The $file parameter must be an instance of UploadedFile or SplFileInfo.');
        }

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
