<?php

namespace App\Http\Controllers;

use App\Actions\StoreNameImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use App\Models\Post; // Importing the Post model
use App\Http\Requests\Post\StoreRequest; // Importing the StoreRequest form request
use App\Http\Requests\Post\UpdateRequest; // Importing the UpdateRequest form request

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $user = Auth::user()->name;

        // Retrieve published and draft posts from the database and pass them to the view
        return response()->view('posts.index', [
            'draftPosts' => Post::where('username', $user)->where('is_published', false)->orderBy('updated_at', 'desc')->get(),
            'publishedPosts' => Post::where('username', $user)->where('is_published', true)->orderBy('updated_at', 'desc')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return the view for creating a new post
        return response()->view('posts.edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request, StoreNameImage $action): RedirectResponse
    {
        // Validate the incoming request
        $validated = $request->validated();

        // If an info file is uploaded, update the file path and delete the old file if exists
        if ($request->hasFile('image_file')) {
           
            $filePath = $action->handle($request, 'image_file', 'files/posts/images/');

            // Add the filepath to validated data
            $validated['image_file'] = $filePath;
        }

        // Get the authenticated user's username
        $username = Auth::user()->name;

        // Add the username to validated data
        $validated['username'] = $username;

        #dd($validated);
        // Create a new post with the validated data
        $create = Post::create($validated);

        if ($create) {
            // Flash a success notification and redirect to the post index page
            session()->flash('notif.success', 'Post created successfully!');
            return redirect()->route('posts.index');
        }

        return abort(500); // Return a server error if the post creation fails
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Retrieve and display the specified post
        return response()->view('posts.show', [
            'post' => Post::findOrFail($id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Retrieve the post with the specified ID
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        if ($post->username !== Auth::user()->name) {
            return abort(403, 'Unauthorized action.');
        }

        // Pass the post to the view for editing
        return response()->view('posts.edit', [
            'post' => $post
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id, StoreNameImage $action): RedirectResponse
    {
        // Find the post with the specified ID
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        if ($post->username !== Auth::user()->name) {
            return abort(403, 'Unauthorized action.');
        }

        // Validate the incoming request
        $validated = $request->validated();

        // If an info file is uploaded, update the file path and delete the old file if exists
        if ($request->hasFile('image_file')) {
            //Delete old file before, if user uploaded one before (so image_file is not empty)
            if (isset($post->image_file)) {
                Storage::disk('public')->delete($post->image_file);
            }
            
            $filePath = $action->handle($request, 'image_file', 'files/posts/images/');


            // Add the filepath to validated data
            $validated['image_file'] = $filePath;
        }

        // Update the post with the validated data
        $update = $post->update($validated);

        if ($update) {
            // Flash a success notification and redirect to the post index page
            session()->flash('notif.success', 'Your Post updated successfully!');
            return redirect()->route('posts.index');
        }

        return abort(500); // Return a server error if the post update fails
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        // Find the post with the specified ID
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        if ($post->username !== Auth::user()->name) {
            return abort(403, 'Unauthorized action.');
        }

        // If an info file exists, delete it from storage
        if (isset($post->image_file)) {
            Storage::disk('public')->delete($post->image_file);
        }

        // Delete the post
        $delete = $post->delete($id);

        if ($delete) {
            // Flash a success notification and redirect to the post index page
            session()->flash('notif.success', 'Post deleted successfully!');
            return redirect()->route('posts.index');
        }

        return abort(500); // Return a server error if the post deletion fails
    }

    /**
     * Mark the specified post as published.
     */
    public function publish(string $id): RedirectResponse
    {
        // Find the post with the specified ID and update its publication status
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        if ($post->username !== Auth::user()->name) {
            return abort(403, 'Unauthorized action.');
        }

        $isPublished = $post->update(['is_published' => true]);

        if ($isPublished) {
            // Flash a success notification and redirect to the post index page
            session()->flash('notif.success', 'Post published successfully!');
            return redirect()->route('posts.index');
        }

        return abort(500); // Return a server error if updating the post fails
    }

    /**
     * Mark the specified post as a draft.
     */
    public function makedraft(string $id): RedirectResponse
    {
        // Find the post with the specified ID and update its publication status
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        if ($post->username !== Auth::user()->name) {
            return abort(403, 'Unauthorized action.');
        }


        $isDrafted = $post->update(['is_published' => false]);

        if ($isDrafted) {
            // Flash a success notification and redirect to the post index page
            session()->flash('notif.success', 'Post saved as draft!');
            return redirect()->route('posts.index');
        }

        return abort(500); // Return a server error if updating the post fails
    }
}
