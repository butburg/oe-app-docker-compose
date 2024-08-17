<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Models\Image;
use App\Actions\CreateImageVariants;
use App\Enums\ImageSizeType;
use Illuminate\Support\Facades\File;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\UploadedFile;



class MigrateLegacyImages extends Command
{
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
    public function handle()
    {
        $legacyImagePath = '/var/www/html/storage/app/public/files/posts/images_legacy';

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

            if (!$post) {
                $this->error("Post not found for image: $fullFileName");
                continue; // Skip to the next file if no matching post is found
            }

            // Create a new image record
            $image = Image::create([
                'post_id' => $post->id,
                'upload_size' => filesize($file),
            ]);


            // Define the desired (wanted) image sizes
            $desiredSizes = [
                's' => ImageSizeType::SMALL->value,
                'm' => ImageSizeType::MEDIUM->value,
                'l' => ImageSizeType::LARGE->value,
                'xl' => ImageSizeType::EXTRA_LARGE->value,
            ];

            // Save models and files in storage
            app(CreateImageVariants::class)->handleVariant($image, $file, $desiredSizes, 'posts/images/');
        }

        $this->info('Legacy images migrated successfully.');
    }
}
