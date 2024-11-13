<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

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
            'comment' =>fake()->paragraph
        ];
    }

    /**
     * Indicate that the comment should belong to a specific user.
     */
    public function forUser(User $user): static
    {
        //The state method in Laravel factories is used to modify the default attributes of the factory.
        //eturns an array that will override or add to the existing attributes of the factory
        return $this->state([
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the comment should belong to a specific post.
     */
    public function forPost(Post $post): static
    {
        return $this->state([
            'post_id' => $post->id,
        ]);
    }
}
