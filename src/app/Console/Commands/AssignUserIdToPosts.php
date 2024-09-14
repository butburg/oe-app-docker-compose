<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Models\User;

class AssignUserIdToPosts extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:assign-user {current_user_name} {legacy_username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a user to his old posts width its legacy username';

    /**
     * Execute the console command.
     */
    public function handle() {

        $currentUserName = $this->argument('current_user_name');
        $legacyUsername = $this->argument('legacy_username');

        // Find the user by name
        $user = User::where('name', $currentUserName)->first();

        if (!$user) {
            $this->error("No user found with name {$currentUserName}");
            return;
        }

        // Fetch posts with null user_id and matching legacy username
        $posts = Post::whereNull('user_id')
            ->where('username', $legacyUsername)
            ->get();

        foreach ($posts as $post) {
            // Assign the found user's ID to the post
            $post->user_id = $user->id;
            $post->save();

            $this->info("Assigned user {$user->name} (ID {$user->id}) to post {$post->title} (ID {$post->id})");
        }

        $this->info('User IDs assigned and posts marked as published successfully.');
    }
}
