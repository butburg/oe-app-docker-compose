<?php

namespace App\Http\Controllers;

use App\Actions\DeleteUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $users = User::withCount('posts', 'comments')->get();
        return view('admin.index', compact('users'));
    }

    /**
     * Delete another (non-admin) user's account. Admins can't delete themselves
     * or other admins here; their own account goes through the profile page.
     */
    public function destroyUser(Request $request, User $user, DeleteUser $deleteUser): RedirectResponse
    {
        abort_if($user->is($request->user()) || $user->usertype === 'admin', 403);

        $request->validateWithBag('adminUserDeletion', [
            'confirm_email' => ['required', 'string', Rule::in([$user->email])],
            'delete_comments' => 'nullable|boolean',
            'delete_posts' => 'nullable|boolean',
        ], [
            'confirm_email.in' => 'The email does not match this user.',
        ]);

        $deleteUser->handle($user, $request->boolean('delete_comments'), $request->boolean('delete_posts'));

        session()->flash('notif.success', "User {$user->email} deleted.");
        return redirect()->route('admin.index');
    }
}
