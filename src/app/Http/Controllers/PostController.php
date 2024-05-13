<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
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
        // Retrieve published and draft posts from the database and pass them to the view
        return response()->view('posts.index', [
            'publishedPosts' => Post::where('is_published', 0)->orderBy('updated_at', 'desc')->get(),
            'draftPosts' => Post::where('is_published', 1)->orderBy('updated_at', 'desc')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return the view for creating a new post
        return response()->view('posts.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the incoming request
        $validated = $request->validated();

        // If a file is uploaded, store it in the public storage
        if ($request->hasFile('info_file')) {
            $filePath = Storage::disk('public')->put('files/posts/info-files', request()->file('info_file'));
            $validated['info_file'] = $filePath;
        }

        // Create a new post with the validated data
        $create = Post::create($validated);

        if($create) {
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
        // Retrieve the post with the specified ID and pass it to the view for editing
        return response()->view('posts.form', [
            'post' => Post::findOrFail($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        // Find the post with the specified ID
        $post = Post::findOrFail($id);
        
        // Validate the incoming request
        $validated = $request->validated();

        // If an info file is uploaded, update the file path and delete the old file if exists
        if ($request->hasFile('info_file')) {
            if (isset($post->info_file)) {
                Storage::disk('public')->delete($post->info_file);
            }
            $filePath = Storage::disk('public')->put('files/posts/info-files', request()->file('info_file'), 'public');
            $validated['info_file'] = $filePath;
        }

        // Update the post with the validated data
        $update = $post->update($validated);

        if($update) {
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

        // If an info file exists, delete it from storage
        if (isset($post->info_file)) {
            Storage::disk('public')->delete($post->info_file);
        }
        
        // Delete the post
        $delete = $post->delete($id);

        if($delete) {
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
        $isPublished = $post->update(['is_published' => true]);

        if($isPublished) {
            // Flash a success notification and redirect to the post index page
            session()->flash('notif.success', 'Post published successfully!');
            return redirect()->route('posts.index');
        }

        return abort(500); // Return a server error if updating the post fails
    }

    /**
     * Mark the specified post as a draft.
     */
    public function draft(string $id): RedirectResponse
    {
        // Find the post with the specified ID and update its publication status
        $post = Post::findOrFail($id);
        $isDrafted = $post->update(['is_published' => false]);

        if($isDrafted) {
            // Flash a success notification and redirect to the post index page
            session()->flash('notif.success', 'Post saved as draft!');
            return redirect()->route('posts.index');
        }

        return abort(500); // Return a server error if updating the post fails
    }

}
