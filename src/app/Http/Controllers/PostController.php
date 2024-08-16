<?php

namespace App\Http\Controllers;

use App\Actions\StoreImage;
use App\Actions\CreateImageVariants;
use App\Enums\ImageSizeType;

use App\Http\Requests\Post\StoreRequest; // Importing the StoreRequest form request
use App\Http\Requests\Post\UpdateRequest; // Importing the UpdateRequest form request

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

use App\Models\Post; // Importing the Post model
use App\Models\Image;
use App\Models\ImageVariant;

use Illuminate\Contracts\View\View;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    protected const IMAGE_DIRECTORY = 'files/posts/images/';
    protected const IMAGE_QUALITY = 90;
    protected array $thumbnailSizes = ['1400', '640'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $userId = Auth::id();

        // Retrieve published and draft posts from the database and pass them to the view
        return view('posts.index', [
            'draftPosts' => Post::whereNotNull('user_id')
                ->where('user_id', $userId)
                ->where('is_published', false)
                ->orderBy('updated_at', 'desc')
                ->paginate(10),

            'publishedPosts' => Post::whereNotNull('user_id')
                ->where('user_id', $userId)
                ->where('is_published', true)
                ->orderBy('updated_at', 'desc')
                ->paginate(10),
        ]);
    }

    /**
     * Display the gallery with pagination.
     */
    public function gallery(Request $request): View
    {
        $posts = Post::where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

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
    public function store(StoreRequest $request, CreateImageVariants $createImageVariants): RedirectResponse
    {
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
            'post_id' => $post->id
        ]);

        // Define the desired (wanted) image sizes
        $desiredSizes = [
            ImageSizeType::SMALL->value,
            ImageSizeType::MEDIUM->value,
            ImageSizeType::LARGE->value,
            ImageSizeType::EXTRA_LARGE->value
        ];

        //save models db and files in storage
        app(CreateImageVariants::class)->handle($image, $imageFile, $desiredSizes);

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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        // Retrieve the post with the specified ID
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        $this->userIsOwner($post->user_id);

        // Pass the post to the view for editing
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id, StoreImage $action): RedirectResponse
    {
        // Find the post with the specified ID
        $post = Post::findOrFail($id);

        // Check if the authenticated user owns the post
        $this->userIsOwner($post->user_id);

        // Validate the incoming request
        $validated = $request->validated();

        // If an info file is uploaded, update the file path  
        /* if ($request->hasFile('image_file')) {
            //extract image id
            $imageNameId = $this->getImageNameId($post->image_file);

            // delete old ones
            $this->deleteOldImages($imageNameId);

            // save new ones
            $filePath = $this->saveImageSizes($request, $action, $imageNameId);

            // Add the filepath to validated data
            $validated['image_file'] = $filePath;
        } */

        // Update the post with the validated data
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

        $this->userIsOwner($post->user_id);

        // If an image file (name) exists in database, delete all versions that contain the ID
        if (isset($post->image_file)) {
            $imageNameId = $this->getImageNameId($post->image_file);
            $this->deleteOldImages($imageNameId);
        }

        // Delete the post
        if ($post->delete($id)) {
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
        $this->userIsOwner($post->user_id);
        $isPublished = $post->update(['is_published' => true]);

        if ($isPublished) {
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
        $this->userIsOwner($post->user_id);
        $isDrafted = $post->update(['is_published' => false]);

        if ($isDrafted) {
            session()->flash('notif.success', 'Post saved as draft!');
            return redirect()->route('posts.index');
        }

        return abort(500); // Return a server error if updating the post fails
    }

    private function getImageNameId(string $filePath): string
    {
        preg_match('/_([a-z0-9]+)\./', $filePath, $matches);
        return $matches[1];
    }

    private function saveImageSizes(Request $request, StoreImage $action, string $fileOutputName): array
    {
        $filePaths = [];

        // generate "full" (limited by action) image size with 100 quality
        $filePaths['original'] = $action->handle($request, 'image_file', self::IMAGE_DIRECTORY, $fileOutputName);

        // Save variants
        foreach ($this->thumbnailSizes as $size) {
            $filePaths[$size] = $action->handle($request, 'image_file', self::IMAGE_DIRECTORY, $fileOutputName, $size, $this->getQuality($size));
        }

        return $filePaths;
    }

    private function getSizeType(string $variant): int
    {
        // Return a numeric value to represent size types
        return match ($variant) {
            'original' => 0,
            'thumbnail' => 1,
            'mobile' => 2,
            'desktop' => 3,
            default => 0,
        };
    }





    private function deleteOldImages(string $imageNameId): bool
    {
        // get all names of the different image sizes with wildcard as an array and delete them
        $filesToDelete = File::glob(storage_path("app/public/" . self::IMAGE_DIRECTORY . "resized_*_{$imageNameId}.jpeg"));
        return File::delete($filesToDelete);
    }

    private function userIsOwner($user_id_from_post): void
    {
        if ($user_id_from_post !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
