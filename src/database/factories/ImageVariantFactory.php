<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ImageVariant;
use App\Models\Image;
use App\Enums\ImageSizeType;

class ImageVariantFactory extends Factory
{
    protected $model = ImageVariant::class;

    // Store imageIndex for path generation
    protected $imageIndex;

    public function definition(): array
    {
        return [];
    }

    /**
     * Set both the image index and the size for the variant.
     * This method will generate the necessary size attributes and path.
     *
     * @param int $imageIndex
     * @param ImageSizeType $size
     * @return $this
     */
    public function withVariantDetails(int $imageIndex, ImageSizeType $size)
    {
        return $this->state(function (array $attributes) use ($imageIndex, $size) {
            return [
                'quality' => $size->getQuality(),
                'size_type' => $size->value,  // Size type as string
                'width' => $size->getMaxWidth(),  // Width from the enum
                'height' => $size->getMaxHeight(),  // Height from the enum
                'path' => "files/images/factory_images/{$size->value}/{$imageIndex}.jpeg",  // Dynamically generated path
            ];
        });
    }

    /**
     * Associate the image variant with a specific image.
     *
     * @param Image $image
     * @return $this
     */
    public function forImage(Image $image)
    {
        return $this->state([
            'image_id' => $image->id,
        ]);
    }
}
