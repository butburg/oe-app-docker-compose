<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Models\Image;
use App\Http\Requests\ProfileUpdateRequest;
use App\Enums\ImageSizeType;
use App\Actions\LastNameChange;
use App\Actions\CreateImageVariants;


class ProfileController extends Controller
{

    public function updateName(ProfileUpdateRequest $request): RedirectResponse
    {

        $user = Auth::user();
        $name = $request->validated()['name'];
        // Check if the name has changed
        if ($user->name === $name) {
            // No change detected, return to the same route with a message
            return Redirect::back()->withErrors(['name' => 'That\'s your name already :P']);
        }

        // Validate the name change interval
        $statusMessage = LastNameChange::getNameChangeStatus($user->last_name_change, 30);
        if ($statusMessage) {
            session()->flash('notif.success', 'Failed!');
            return Redirect::back()->withErrors(['name' => $statusMessage]);
        }

        // Update previous_name and last_name_change
        $user->previous_name = $user->name;
        $user->last_name_change = now();

        // Update the username
        $user->name = $name;
        $user->save();


        session()->flash('notif.success', 'Your username updated successfully!');
        return Redirect::route('profile.edit');
    }

    public function updateDescription(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $description = $request->validated()['description'];

        $user->description = $description;
        $user->save();

        session()->flash('notif.success', 'Your description updated successfully!');
        return Redirect::route('profile.edit');
    }

    public function updateEmail(ProfileUpdateRequest $request): RedirectResponse
    {

        $user = Auth::user();
        $email = $request->validated()['email'];

        if ($user->email === $email) {
            // No change detected, return to the same route with a message
            return Redirect::back()->withErrors(['email' => 'That\'s your email already :P']);
        }

        // Handle email verification reset 
        $user->email_verified_at = null;
        $user->email = $email;
        $user->save();

        session()->flash('notif.success', 'Your mail updated successfully!');
        return Redirect::route('profile.edit');
    }

    /**
     * Update the user's profile image.
     */
    public function updateImage(ProfileUpdateRequest $request, CreateImageVariants $createImageVariants): RedirectResponse
    {

        $user = Auth::user();
        $imageFile = $request->validated()['profile_image'];

        // Check if the user already has a profile image
        $image = $user->image ?: new Image(['user_id' => $user->id]);

        // Update the upload_size
        $image->upload_size = $imageFile->getSize();
        $image->save();

        // Define the desired (wanted) image sizes
        $desiredSizes = [
            ImageSizeType::EXTRA_SMALL->value,
            ImageSizeType::SMALL->value,
            ImageSizeType::LARGE->value,
        ];

        // Save models in the database and files in storage
        $createImageVariants->handleVariant($image, $imageFile, $desiredSizes, 'profiles/');

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }


    public function show(User $user): View
    {
        // Fetch the user information
        $user = User::withCount('posts', 'comments')->findOrFail($user->id);

        // Get the user's posts with pagination
        $posts = $user->posts()
            ->where('is_published', true)  // Only published posts
            ->withCount('comments') // Eager load comments count for each post
            ->orderBy('created_at', 'desc')
            ->paginate(config('app.posts_per_page')); // Change this to the desired number of posts per page

        // Return the view with user data and posts
        return view('profile.show', compact('user', 'posts'));
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
            'delete_comments' => 'nullable|boolean',
            'delete_posts' => 'nullable|boolean',
        ]);

        $user = $request->user();
        \DB::beginTransaction();
        try {
            if ($user->image) {
                $image = $user->image;

                // Delete the image variants from storage
                foreach ($image->variants as $variant) {
                    Storage::disk('public')->delete($variant->path);
                }

                // Delete the image record (this will cascade and delete associated variants)
                $image->delete();
            }

            // Handle deletion options
            if ($request->boolean('delete_comments')) {
                // Delete comments associated with the user
                $user->comments()->delete();
            }

            if ($request->boolean('delete_posts')) {
                // Delete posts associated with the user and their images
                foreach ($user->posts as $post) {
                    // Delete the post images
                    if ($post->image) {
                        // Delete the image variants from storage
                        foreach ($post->image->variants as $variant) {
                            Storage::disk('public')->delete($variant->path);
                        }

                        // Delete the image record
                        $post->image->delete();
                    }

                    // Delete the post itself
                    $post->delete();
                }
            }

            Auth::logout();

            $user->delete();


            $request->session()->invalidate();
            $request->session()->regenerateToken();
            \DB::commit();
            return Redirect::to('/');
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            \DB::rollBack();

            // Log the error
            Log::error('User deletion failed: ' . $e->getMessage());

            // Optionally, redirect back with an error message
            return back()->withErrors(['error' => 'There was an issue deleting your account.']);
        }
    }
}
