<?php

namespace App\Http\Controllers;

use App\Actions\CreateImageVariants;
use App\Enums\ImageSizeType;
use App\Http\Requests\Post\StoreRequest; // Importing the StoreRequest form request
use App\Http\Requests\Post\UpdateRequest; // Importing the UpdateRequest form request
use App\Models\Image;
use App\Models\Post; // Importing the Post model
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(): View {
        $userId = Auth::id();
        $userType = Auth::user()->usertype;

        // Retrieve published and draft posts from the database
        $draftPosts = Post::whereNotNull('user_id')
            ->where('user_id', $userId)
            ->where('once_published', false)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        $publishedPosts = Post::whereNotNull('user_id')
            ->where('user_id', $userId)
            ->where('once_published', true)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('posts.index', [
            'draftPosts' => $draftPosts,
            'publishedPosts' => $publishedPosts,
            'isAdmin' => $userType === 'admin',
        ]);
    }
    /**
     * Display a special listing of the resources with all only for admin.
     */
    public function all(): View {
        // Retrieve published and draft posts from the database and pass them to the view
        return view('posts.index', [
            'draftPosts' => Post::where('is_published', false)
                ->orderBy('updated_at', 'desc')
                ->paginate(15),
            'publishedPosts' => Post::where('is_published', true)
                ->orderBy('updated_at', 'desc')
                ->paginate(15),
        ]);
    }
    /**
     * Display the gallery with pagination.
     */
    public function gallery(Request $request): View {
        $posts = Post::where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('welcome', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View {
        // Return the view for creating a new post
        return view('posts.edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request, CreateImageVariants $createImageVariants): RedirectResponse {
        // Validate the incoming request
        $validated = $request->validated();

        // Remove the image file from the validated data
        $imageFile = $validated['image_file'];
        unset($validated['image_file']);

        // Add the user who uploads the image to validated data
        $validated['user_id'] = Auth::id();
        $validated['username'] = Auth::user()->name;

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
    public function show(string $id): View {
        $post = Post::findOrFail($id);
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View {
        // Retrieve the post with the specified ID
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        $this->userIsOwner($post->user_id);

        // Restrict edit access based on the once_published flag and user role
        if ($post->once_published && Auth::user()->usertype !== 'admin') {
            session()->flash('notif.error', 'You cannot edit this post since it has been published.');
            return redirect()->route('posts.index');
        }

        // Pass the post to the view for editing
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id, CreateImageVariants $createImageVariants): RedirectResponse {
        // Find the post with the specified ID
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        $this->userIsOwner($post->user_id);

        // Prevent editing title and image if the post was once published and the user is not an admin
        if ($post->once_published && Auth::user()->usertype !== 'admin') {
            $request->except(['title', 'image_file']);
        }

        // Validate the incoming request
        $validated = $request->validated();

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
        }

        if ($post->update($validated)) {
            session()->flash('notif.success', 'Your Post updated successfully!');
            return redirect()->route('posts.index');
        }

        return abort(500); // Return a server error if the post update fails
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse {
        $post = Post::findOrFail($id);

        $this->userIsOwner($post->user_id);

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
    public function publish(string $id): RedirectResponse {
        // Find the post with the specified ID and update its publication status
        $post = Post::findOrFail($id);

        $this->userIsOwner($post->user_id);

        // Update both is_published and once_published
        $isPublished = $post->update(['is_published' => true, 'once_published' => true]);

        if ($isPublished) {
            session()->flash('notif.success', 'Post published successfully!');
            return redirect()->route('posts.index');
        }
        return abort(500); // Return a server error if updating the post fails
    }

    /**
     * Mark the specified post as a draft.
     */
    public function makedraft(string $id): RedirectResponse {
        // Find the post with the specified ID and update its publication status
        $post = Post::findOrFail($id);
        // Check if the authenticated user owns the post
        $this->userIsOwner($post->user_id);

        // Allow only admins to revert to draft if the post has been published
        if ($post->once_published && Auth::user()->usertype !== 'admin') {
            session()->flash('notif.error', 'You cannot unpublish a post that has already been published.');
            return redirect()->route('posts.index');
        }


        $isDrafted = $post->update(['is_published' => false]);

        if ($isDrafted) {
            session()->flash('notif.success', 'Post saved as draft!');
            return redirect()->route('posts.index');
        }
        return abort(500); // Return a server error if updating the post fails
    }

    /*
    * Hide the specified post (unpublish it without making it a draft).
    */
    public function hide(string $id): RedirectResponse {
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        $this->userIsOwner($post->user_id);

        // Allow hiding only if the post was once published
        if ($post->once_published) {
            $isHidden = $post->update(['is_published' => false]);

            if ($isHidden) {
                session()->flash('notif.success', 'Post hidden successfully!');
                return redirect()->route('posts.index');
            }

            return abort(500); // Return a server error if updating the post fails
        }

        session()->flash('notif.error', 'Only published posts can be hidden.');
        return redirect()->route('posts.index');
    }

    private function userIsOwner($user_id_from_post): void {
        if ($user_id_from_post !== Auth::id() and Auth::user()->usertype !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
    }
}
