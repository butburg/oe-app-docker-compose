<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Post;
use App\Models\Image;


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
    public function definition(): array
    {

        $isPublished = fake()->boolean(75); // 75% chance of being published

        return [
            'title' => fake()->sentence(),
            'is_published' => $isPublished,
            'is_sensitive' => fake()->boolean(10), // 10% chance of being sensitive
            'published_at' => $isPublished ? fake()->dateTimeThisYear() : null, // Set 'published_at' only if published
            'username' => 'no user set',  // will be overwritten from forUser
        ];
    }

    public function withImage()
    {
        return $this->afterCreating(function (Post $post) {
            Image::factory()->count(1)->for($post)->create();
        });
    }

    /**
     * Indicate that the post should be for a specific user.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forUser(User $user)
    {
        return $this->state([
            'user_id' => $user->id,
            'username' => $user->name,
        ]);
    }
}
