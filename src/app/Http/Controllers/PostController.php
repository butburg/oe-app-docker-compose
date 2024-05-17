<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\Post; // Importing the Post model
use App\Http\Requests\Post\StoreRequest; // Importing the StoreRequest form request
use App\Http\Requests\Post\UpdateRequest; // Importing the UpdateRequest form request
use Intervention\Image\Laravel\Facades\Image; // Import Image facade

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve published and draft posts from the database and pass them to the view
        return response()->view('posts.index', [
            'draftPosts' => Post::where('is_published', false)->orderBy('updated_at', 'desc')->get(),
            'publishedPosts' => Post::where('is_published', true)->orderBy('updated_at', 'desc')->get(),
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
    public function store(StoreRequest $request): RedirectResponse
    {
        // Validate the incoming request
        $validated = $request->validated();

        // If a file is uploaded, store it in the public storage
        if ($request->hasFile('info_file')) {
            // Get the uploaded file
            $file = $request->file('info_file');
            
            // resize and format the image
            $resizedImage = Image::read($file)->scale(1400,1400)->toJpeg(quality: 100, progressive: true);

            // Generate a unique filename
            $extension = explode('/', $resizedImage->mimetype())[1];
            $filename = uniqid('resized_') . '_' . $file->getClientOriginalName() . '.' . $extension;
            
            
            // Store the resized image
            $filePath = 'files/posts/info-files/' . $filename;
            Storage::disk('public')->put($filePath, $resizedImage);

            // Add the filepath to validated data
            $validated['info_file'] = $filePath;
        }

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
        // Retrieve the post with the specified ID and pass it to the view for editing
        return response()->view('posts.form', [
            'post' => Post::findOrFail($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        // Find the post with the specified ID
        $post = Post::findOrFail($id);

        // Validate the incoming request
        $validated = $request->validated();

        // If an info file is uploaded, update the file path and delete the old file if exists
        if ($request->hasFile('info_file')) {
            //Delete old file before, if user uploaded one before (so info_file is not empty)
            if (isset($post->info_file)) {
                Storage::disk('public')->delete($post->info_file);
            }

            // Get the uploaded file
            $file = $request->file('info_file');
            
            // resize and format the image
            $resizedImage = Image::read($file)->scale(1400,1400)->toJpeg(quality: 100, progressive: true);

            // Generate a unique filename
            $extension = explode('/', $resizedImage->mimetype())[1];
            $filename = uniqid('resized_') . '_' . $file->getClientOriginalName() . '.' . $extension;
            
            
            // Store the resized image
            $filePath = 'files/posts/info-files/' . $filename;
            Storage::disk('public')->put($filePath, $resizedImage);

            // Add the filepath to validated data
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
    public function makedraft(string $id): RedirectResponse
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
