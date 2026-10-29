<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteUser
{
    /**
     * Delete a user with their profile image and, optionally, their comments and posts.
     * Kept posts and comments stay anonymous (user_id is set to null by the foreign key).
     */
    public function handle(User $user, bool $deleteComments = false, bool $deletePosts = false): void
    {
        DB::transaction(function () use ($user, $deleteComments, $deletePosts) {
            if ($user->image) {
                // Delete the image variants from storage
                foreach ($user->image->variants as $variant) {
                    Storage::disk('public')->delete($variant->path);
                }

                // Delete the image record (this will cascade and delete associated variants)
                $user->image->delete();
            }

            if ($deleteComments) {
                $user->comments()->delete();
            }

            if ($deletePosts) {
                foreach ($user->posts as $post) {
                    if ($post->image) {
                        foreach ($post->image->variants as $variant) {
                            Storage::disk('public')->delete($variant->path);
                        }
                        $post->image->delete();
                    }
                    $post->delete();
                }
            }

            // Sessions have no foreign key, so log the user out everywhere explicitly
            DB::table('sessions')->where('user_id', $user->id)->delete();

            $user->delete();
        });
    }
}
