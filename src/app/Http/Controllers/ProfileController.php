<?php

namespace App\Http\Controllers;

use App\Actions\CreateImageVariants;
use App\Actions\LastNameChange;
use App\Enums\ImageSizeType;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller {
    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse {
        $user = $request->user();
        $validatedData = $request->validated();

        // Check if the name has changed
        if ($user->name !== $validatedData['name']) {

            // Validate the name change interval
            $statusMessage = LastNameChange::getNameChangeStatus($user->last_name_change, 30);
            if ($statusMessage) {
                return Redirect::back()->withErrors(['name' => $statusMessage]);
            }

            // Update previous_name and last_name_change
            $user->previous_name = $user->name;
            $user->last_name_change = now();
        }

        // Update user profile information
        $user->fill($validatedData);

        // Handle email verification reset if email has changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }


    /**
     * Update the user's profile image.
     */
    public function updateImage(FormRequest $request, CreateImageVariants $createImageVariants): RedirectResponse {
        $validated = $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $imageFile =  $validated['profile_image'];

        $user = Auth::user();

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
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse {
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
