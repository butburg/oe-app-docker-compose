<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\JpegEncoder;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        //$user = User::factory()->create();

        // URL path to the saved image
        $imageUrl = 'files/images/placeholder.jpg';

        return [
            'title' => $this->faker->sentence,
            'image_file' => $imageUrl, // Set to null initially, as we're not providing any file data
            'is_published' => $this->faker->boolean(75), // 75% chance of being published
            'is_sensitive' => $this->faker->boolean(10), // 10% chance of being sensitive
            //'user_id' => $user->id,
            //'username' => $user->name, // Get the username from the user factory
        ];
    }


    /**
     * Indicate that the post should be for a specific user.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
                'username' => $user->name,
            ];
        });
    }
}
