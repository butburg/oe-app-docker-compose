<?php

namespace App\Http\Controllers;

use App\Actions\CreateImageVariants;
use App\Enums\ImageSizeType;
use App\Http\Requests\Post\StoreUpdateRequest;
use App\Models\Image;
use App\Models\Post; // Importing the Post model
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $userId = Auth::id();
        $userType = Auth::user()->usertype;

        // Retrieve published and draft posts from the database
        $draftPosts = Post::whereNotNull('user_id')
            ->where('user_id', $userId)
            ->where('is_published', false)
            ->orderBy('created_at', 'desc')
            ->paginate(config('app.posts_per_page'), pageName: 'draft');

        $publishedPosts = Post::whereNotNull('user_id')
            ->where('user_id', $userId)
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(config('app.posts_per_page'), pageName: 'published');

        return view('posts.index', [
            'draftPosts' => $draftPosts,
            'publishedPosts' => $publishedPosts,
            'isAdmin' => $userType === 'admin',
        ]);
    }

    /**
     * Display a special listing of the resources with all only for admin.
     */
    public function all(): View
    {
        // Retrieve draft posts sorted by created_at
        $draftPosts = Post::where('is_published', false)
            ->orderBy('created_at', 'desc')
            ->paginate(config('app.posts_per_page'));

        // Retrieve published posts sorted by published_at
        $publishedPosts = Post::where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(config('app.posts_per_page'));

        return view('posts.index', [
            'draftPosts' => $draftPosts,
            'publishedPosts' => $publishedPosts,
        ]);
    }

    /**
     * Display the gallery with pagination.
     */
    public function gallery(Request $request): View
    {
        if (Auth::check()) {
            $posts = Post::where('is_published', true)
                ->orderBy('published_at', 'desc')
                ->paginate(config('app.posts_per_page'));
        } else {
            $posts = Post::where('is_published', true)
                ->where('is_sensitive', false)
                ->orderBy('published_at', 'desc')
                ->paginate(config('app.posts_per_page'));
        }

        return view('welcome', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // Return the view for creating a new post
        return view('posts.edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUpdateRequest $request, CreateImageVariants $createImageVariants): RedirectResponse
    {
        // Validate the incoming request
        $validated = $request->validated();

        // Remove the image file from the validated data
        $imageFile = $validated['image_file'];
        unset($validated['image_file']);

        // Add the user who uploads the image to validated data
        $validated['user_id'] = Auth::id();
        $validated['username'] = Auth::user()->name;

        // Ensure that is_sensitive is set to 0 if not checked
        $validated['is_sensitive'] = $request->has('is_sensitive') ? 1 : 0;

        // Create the post
        $post = Post::create($validated);

        // Create the image in database and reference image with post
        // Is implement for adding more than one image or more metadata later
        $image = Image::create([
            'post_id' => $post->id,
            'upload_size' => $imageFile->getSize()
        ]);

        // Define the desired (wanted) image sizes
        $desiredSizes = [
            ImageSizeType::SMALL->value,
            ImageSizeType::MEDIUM->value,
            ImageSizeType::LARGE->value,
            ImageSizeType::EXTRA_LARGE->value
        ];

        //save models db and files in storage
        app(CreateImageVariants::class)->handleVariant($image, $imageFile, $desiredSizes);

        if ($post) {
            // Flash a success notification and redirect to the post index page
            session()->flash('notif.success', 'Post created successfully!');
            return redirect()->route('posts.index');
        }

        return abort(500); // Return a server error if the post creation fails
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $post = Post::findOrFail($id);
        return view('posts.show', compact('post'));
    }

    /**
     * Calculate the gallery page number for a specific post that is PUBLISHED.
     */
    public function getPageForPost(Post $post, $perPage = null)
    {
        if (!$post->is_published) {
            throw new \Exception("Cannot calculate page for unpublished post");
        }

        // Use the configuration value if $perPage is null
        $perPage = $perPage ?? config('app.posts_per_page');

        // Get the total number of posts before the current post in descending order
        $postPosition = Post::where('is_published', true)
            ->where('published_at', '>=', $post->published_at)
            ->orderBy('published_at', 'desc')
            ->count();

        // Calculate the page number
        $page = ceil(($postPosition + 1) / $perPage);

        return $page;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        // Retrieve the post with the specified ID
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        $this->userIsOwnerOrAdmin($post->user_id);

        // Restrict edit access based on the is_published flag and user role
        if ($post->is_published && Auth::user()->usertype !== 'admin') {
            session()->flash('notif.error', 'You cannot edit this post since it has been published.');
            return redirect()->route('posts.index');
        }

        // Pass the post to the view for editing
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreUpdateRequest $request, string $id, CreateImageVariants $createImageVariants): RedirectResponse
    {
        // Find the post with the specified ID
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        $this->userIsOwnerOrAdmin($post->user_id);

        // Prevent editing title and image if the post was once published and the user is not an admin
        if ($post->is_published && Auth::user()->usertype !== 'admin') {
            $request->except(['title', 'image_file']);
        }

        // Validate the incoming request
        $validated = $request->validated();

        // Ensure that is_sensitive is set to 0 if not checked
        $validated['is_sensitive'] = $request->has('is_sensitive') ? 1 : 0;

        // Check if an image file is uploaded
        if ($request->hasFile('image_file')) {
            $imageFile = $validated['image_file'];
            unset($validated['image_file']);

            $imageRecord = $post->image;

            // Update the upload size
            $imageRecord->upload_size = $imageFile->getSize();
            $imageRecord->save();

            // Define the desired (wanted) image sizes
            $desiredSizes = [
                ImageSizeType::SMALL->value,
                ImageSizeType::MEDIUM->value,
                ImageSizeType::LARGE->value,
                ImageSizeType::EXTRA_LARGE->value
            ];

            // Save models in the database and files in storage
            $createImageVariants->handleVariant($imageRecord, $imageFile, $desiredSizes);
        } // end if image file is uploaded

        if ($post->update($validated)) {
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
        $post = Post::findOrFail($id);

        $this->userIsOwnerOrAdmin($post->user_id);

        // Check if the post has an associated image
        if ($post->image) {
            $image = $post->image;

            // Delete the image variants from storage
            foreach ($image->variants as $variant) {
                Storage::disk('public')->delete($variant->path);
            }

            // Delete the image record (this will cascade and delete associated variants)
            $image->delete();
        }

        // Delete the post
        if ($post->delete()) {
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

        $this->userIsOwnerOrAdmin($post->user_id);

        // Check if the post has already been published once
        if (!$post->once_published) {
            // First time publishing, set the published_at date
            $post->published_at = now();
        }

        // Update post
        $isPublished = $post->update([
            'is_published' => true,
            'published_at' => $post->published_at
        ]);

        if ($isPublished) {
            session()->flash('notif.success', 'Post published successfully!');
            return redirect()->route('posts.index');
        }
        return abort(500); // Return a server error if updating the post fails
    }

    /**
     * Mark the specified post as a draft. Only for admins.
     */
    public function makedraft(string $id): RedirectResponse
    {
        // Find the post with the specified ID and update its publication status
        $post = Post::findOrFail($id);

        // Admins can always revert posts to draft
        $isDrafted = $post->update(['is_published' => false]);

        if ($isDrafted) {
            session()->flash('notif.success', 'Post reverted to draft successfully!');
            return redirect()->route('posts.index');
        }

        return abort(500); // Return a server error if updating the post fails
    }

    /**
     * Toggle the sensitivity of the specified post.
     */
    public function toggleSensitive(string $id): RedirectResponse
    {
        // Find the post with the specified ID
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        $this->userIsOwnerOrAdmin($post->user_id);

        // Toggle the sensitivity status
        $post->is_sensitive = !$post->is_sensitive;
        $post->save();

        // Flash a success notification and redirect to the post index page
        session()->flash('notif.success', 'Post sensitivity toggled successfully!');
        return redirect()->route('posts.index');
    }


    private function userIsOwnerOrAdmin($user_id_from_post): void
    {
        if ($user_id_from_post !== Auth::id() and Auth::user()->usertype !== 'admin') {
            abort(403, 'Unauthorized action. You are not the owner of the post.');
        }
    }
}
