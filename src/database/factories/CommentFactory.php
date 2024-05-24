<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Post;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{


    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comment' => $this->faker->paragraph,
            //'user_id' => User::factory(), // Create a new user for each comment
            //'post_id' => Post::factory(), // Create a new post for each comment

        ];
    }

    /**
     * Indicate that the comment should belong to a specific user.
     */
    public function forUser(User $user)
    {
        //The state method in Laravel factories is used to modify the default attributes of the factory.
        //array $attributes: This parameter represents the current attributes of the factory instance.
        return $this->state(function (array $attributes) use ($user) {
            //eturns an array that will override or add to the existing attributes of the factory
            return [
                'user_id' => $user->id,
            ];
        });
    }

    /**
     * Indicate that the comment should belong to a specific post.
     */
    public function forPost(Post $post)
    {
        return $this->state(function (array $attributes) use ($post) {
            return [
                'post_id' => $post->id,
            ];
        });
    }
}
