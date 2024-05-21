<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'title' => $this->faker->sentence,
            'info_file' => null, // Set to null initially, as we're not providing any file data
            'is_published' => $this->faker->boolean(75), // 75% chance of being published
            'is_sensitive' => $this->faker->boolean(10), // 10% chance of being sensitive
        ];
    }
}