<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Post;
use App\Models\ImageVariant;
use App\Models\Image;
use App\Enums\ImageSizeType;

class ImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition(): array
    {
        return [
            'upload_size' => fake()->numberBetween(100, 5000), // size in KB
        ];
    }

    public function configure(): static
    {
        // Automatically create four variants for each image by default (s, m, l, xl)
        return $this->afterCreating(function (Image $image) {
            // Define the size types
            $sizes = [
                ImageSizeType::SMALL,
                ImageSizeType::MEDIUM,
                ImageSizeType::LARGE,
                ImageSizeType::EXTRA_LARGE,
            ];

            // Create one variant for each size
            $linkToFile = fake()->numberBetween(1, 6);
            foreach ($sizes as $size) {
                ImageVariant::factory()
                    ->withVariantDetails($linkToFile, $size)
                    ->forImage($image)
                    ->create();
            }
        });
    }

    public function forPost(Post $post)
    {
        return $this->state([
            'post_id' => $post->id,
        ]);
    }

    public function forUser(User $user)
    {
        return $this->state([
            'user_id' => $user->id,
        ]);
    }
}
