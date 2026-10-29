<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Models\Image;
use App\Http\Requests\ProfileUpdateRequest;
use App\Enums\ImageSizeType;
use App\Actions\LastNameChange;
use App\Actions\CreateImageVariants;
use App\Actions\DeleteUser;


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

    /**
     * Own URL of the "Mail notifications" section, linked from the mails. A guest logs in first and comes back here,
     * an #anchor alone would get lost on the way through the login.
     */
    public function notifications(): RedirectResponse
    {
        return redirect()->to(route('profile.edit') . '#notifications');
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        $settings = array_keys(User::NOTIFICATION_SETTINGS);
        $validated = $request->validate(array_fill_keys($settings, 'nullable|boolean'));

        $request->user()->forceFill(
            collect($settings)->mapWithKeys(fn ($setting) => [$setting => (bool) ($validated[$setting] ?? false)])->all()
        )->save();

        return redirect()->to(route('profile.edit') . '#notifications')->with('status', 'notifications-updated');
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

        // Only noted as pending, the current email stays active and verified until the link is opened
        $user->requestEmailChange($email);

        session()->flash('notif.success', 'We sent a link to ' . $email . '. Your current email stays active until you open it.');
        return Redirect::route('profile.edit');
    }

    /**
     * Send the link for the pending email again.
     */
    public function resendEmailChange(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->pending_email) {
            $user->sendEmailChangeLink();
            session()->flash('notif.success', 'We sent the link to ' . $user->pending_email . ' again.');
        }

        return Redirect::route('profile.edit');
    }

    /**
     * Drop the pending email, its link stops working and the current email stays.
     */
    public function cancelEmailChange(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->pending_email = null;
        $user->save();

        session()->flash('notif.success', 'The email change was cancelled, you keep ' . $user->email . '.');
        return Redirect::route('profile.edit');
    }

    /**
     * The link in the mail only shows a button, so a mail scanner that opens it confirms nothing.
     */
    public function showEmailChange(Request $request, User $user, string $hash): View|RedirectResponse
    {
        if ($user->pending_email && hash_equals(sha1($user->pending_email), $hash)) {
            // The signed query string is kept, the POST goes through the same signature check
            return view('profile.confirm-email', ['user' => $user, 'action' => $request->fullUrl()]);
        }

        // Nothing to confirm: confirmEmailChange only answers with the matching message
        return $this->confirmEmailChange($request, $user, $hash);
    }

    /**
     * Switch to the pending email from the button on the link page. Needs no login, the signed
     * link is the proof, so it also works on another device.
     */
    public function confirmEmailChange(Request $request, User $user, string $hash): RedirectResponse
    {
        $target = Auth::check() ? Redirect::route('profile.edit') : Redirect::route('login');

        // Already switched by an earlier click on the link
        if (hash_equals(sha1($user->email), $hash)) {
            $message = 'Your email is already changed to ' . $user->email . '.';
            return Auth::check() ? $target->with('notif.success', $message) : $target->with('status', $message);
        }

        if (!$user->pending_email || !hash_equals(sha1($user->pending_email), $hash)) {
            return $target->withErrors(['email' => 'This link does not work any more. Your email was not changed.']);
        }

        // Someone may have registered the address in the meantime
        if (User::where('email', $user->pending_email)->exists()) {
            $user->pending_email = null;
            $user->save();
            return $target->withErrors(['email' => 'This email is used by another account by now. Your email was not changed.']);
        }

        $user->forceFill([
            'email' => $user->pending_email,
            'pending_email' => null,
            'email_verified_at' => now(),
        ])->save();

        $message = 'Your email was changed to ' . $user->email . '.';
        // Logged in: banner in the app layout, otherwise the status line on the login page
        return Auth::check() ? $target->with('notif.success', $message) : $target->with('status', $message);
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
     * Delete the account from the verify page, the profile is closed to unverified users.
     */
    public function destroyUnverified(Request $request, DeleteUser $deleteUser): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return Redirect::route('profile.edit');
        }

        return $this->destroy($request, $deleteUser);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, DeleteUser $deleteUser): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
            'delete_comments' => 'nullable|boolean',
            'delete_posts' => 'nullable|boolean',
        ]);

        $user = $request->user();
        try {
            // Log out first: logout() saves a new remember token, which would re-insert a deleted user
            Auth::logout();

            $deleteUser->handle($user, $request->boolean('delete_comments'), $request->boolean('delete_posts'));

            $request->session()->invalidate();
            $request->session()->regenerateToken();
            session()->flash('notif.success', 'Your account was deleted.');
            return Redirect::to('/');
        } catch (\Exception $e) {
            // Log the error
            Log::error('User deletion failed: ' . $e->getMessage());

            // The user is logged out already but the account still exists (transaction),
            // so say so on the login page instead of a back() that ends there without a message
            return Redirect::route('login')
                ->with('status', 'Your account could not be deleted. Please log in and try again.');
        }
    }
}
