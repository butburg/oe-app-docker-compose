<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Image;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the AdminSeeder
        $this->call(AdminSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PostSeeder::class);
        $this->call(CommentSeeder::class);
    }
}

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@user.com',
            'password' => bcrypt('admin'), // Hash the password using bcrypt
            'usertype' => 'admin',
            'description' => fake()->sentence(),
            'email_verified_at' => now(),
        ]);

        // Create for Admin a profile image
        Image::factory()->count(1)->forUser($admin)->create();
    }
}

class UserSeeder extends Seeder
{
    /**
     * Run the user seeding process.
     */
    public function run(): void
    {
        // Create specific users
        $users = User::factory(4)->create();
    }
}

class PostSeeder extends Seeder
{
    /**
     * Run the post seeding process.
     */
    public function run(): void
    {
        $users = User::all();
        // Create posts for these users
        $users->each(function ($user) {
            Post::factory()->count(6)->forUser($user)->withImage()->create();
        });
    }
}

class CommentSeeder extends Seeder
{
    /**
     * Run the comment seeding process.
     */
    public function run(): void
    {
        // Retrieve all posts and create comments
        $posts = Post::all();
        $users = User::all();

        // Iterate over each user
        $users->each(function (User $user) use ($posts) {
            // Randomly select 3 posts for the user to comment on
            $randomPosts = $posts->random(6);

            $randomPosts->each(function (Post $post) use ($user) {
                Comment::factory()->count(1)->forUser($user)->forPost($post)->create();
            });
        });
    }
}
