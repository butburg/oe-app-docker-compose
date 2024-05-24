<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Comment;
use App\Models\Post;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create specific users
        $users = User::factory(5)->create();

        // Create posts for these users
        $users->each(function ($user) {
            Post::factory()->count(3)->forUser($user)->create();
        });

        // Create comments for these posts
        $posts = Post::all();
        // Iterate over each user
        $users->each(function ($user) use ($posts) {
            // Randomly select 3 posts for the user to comment on
            $randomPosts = $posts->random(3);
            $randomPosts->each(function ($post) use ($user) {
                Comment::factory()->count(1)->forUser($user)->forPost($post)->create();
            });
        });


        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('12345678'), // Hash the password using bcrypt
        ]);
    }
}
