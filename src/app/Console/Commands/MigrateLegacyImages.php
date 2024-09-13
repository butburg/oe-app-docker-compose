<?php

namespace App\Console\Commands;

use App\Actions\CreateImageVariants;
use App\Enums\ImageSizeType;
use App\Models\Image;
use App\Models\ImageVariant;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;


class MigrateLegacyImages extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-legacy-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate old images from legacy directory to the new image system';

    /**
     * Execute the console command.
     */
    public function handle() {
        $legacyImagePath = storage_path('app/public/files/posts/images_legacy');

        // Check if the directory exists
        if (!File::exists($legacyImagePath)) {
            $this->error("Legacy image directory does not exist: $legacyImagePath");
            return 1; // Return error code
        }

        // Fetch all legacy image files
        $files = File::allFiles($legacyImagePath);


        foreach ($files as $file) {
            // Extract file information
            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $extension = $file->getExtension();
            $fullFileName = $filename . '.' . $extension;

            // Find the post by title
            $post = Post::where('title', $filename)->first();


            $this->info("Processing file: $fullFileName");
            $this->info("Post found: " . json_encode($post));


            if (!$post) {
                $this->error("Post not found for image: $fullFileName");
                continue; // Skip to the next file if no matching post is found
            }

            // Check if the image already exists for the post
            $image = Image::where('post_id', $post->id)->first();

            $this->info("Existing image: " . json_encode($image));


            // if ($post->id == 35) {
            //     dd($post, $image);
            // }

            if ($image) {
                // If the image exists, log it
                $this->info("Image for post '{$post->title}' already exists.");
            } else {
                // If the image doesn't exist, create it
                $image = Image::create([
                    'post_id' => $post->id,
                    'upload_size' => filesize($file),
                ]);

                $this->warn("Image for post '{$post->title}' has been created.");
            }

            // Define the desired (wanted) image sizes
            $desiredSizes = [
                's' => ImageSizeType::SMALL->value,
                'm' => ImageSizeType::MEDIUM->value,
                'l' => ImageSizeType::LARGE->value,
                'xl' => ImageSizeType::EXTRA_LARGE->value,
            ];

            // Check for missing variants and create them if necessary
            foreach ($desiredSizes as $sizeKey => $sizeTypeValue) {
                $variant = ImageVariant::where('image_id', $image->id)
                    ->where('size_type', $sizeTypeValue)
                    ->first();

                if (!$variant) {
                    // If the variant does not exist, create it
                    app(CreateImageVariants::class)->handleVariant($image, $file, [$sizeKey => $sizeTypeValue], 'posts/images/');
                    $this->warn("Created missing variant: $sizeKey for image: $fullFileName");
                }
            }
        }

        $this->info('Legacy images migrated successfully.');
    }
}
