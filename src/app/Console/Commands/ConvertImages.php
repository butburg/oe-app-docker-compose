<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use League\Csv\Reader;

class ConvertImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:convert-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert BLOB data to images and store in storage';

    /**
     * Convert MIME type to file extension
     *
     * @param string $mimeType
     * @return string
     */
    protected function getExtensionFromMimeType($mimeType)
    {
        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
            'image/webp' => 'webp',
            // Add more MIME types and their extensions as needed
        ];

        return isset($mimeTypes[$mimeType]) ? $mimeTypes[$mimeType] : '';
    }
    public function handle()
    {

        $csvFile = 'old_image_posts/images.csv';
        // Open the CSV file for reading
        if (($handle = fopen($csvFile, 'r')) !== false) {
            // Read the header row
            $headers = fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                // Map the row to an associative array using headers
                $data = array_combine($headers, $row);

                // Extract data from the row
                $filename = $data['filename'];
                $mimeType = $data['mime_type'];
                $fileData = $data['file_data'];

                // Decode base64-encoded image data
                $imageData = base64_decode($fileData);

                // Determine file extension based on mime type
                $extension = $this->getExtensionFromMimeType($mimeType);

                // Generate unique filename
                $newFilename = uniqid() . '.' . $extension;

                // Save image to Laravel public storage
                $savePath = 'public/images/' . $newFilename;
                Storage::put($savePath, $imageData);

                // Output success message
                $this->info("Image '$filename' imported and saved as '$newFilename'");
            }

            fclose($handle);
        }

        $this->info('Import process completed.');
    }


    /* public function handle()
    {
        // Fetch data from the old SQLite table
        $oldPosts = DB::connection('old_sqlite')
            ->table('images')
            ->select('filename', 'mime_type', 'file_data')
            ->get();

        foreach ($oldPosts as $oldPost) {
            // Get the correct file extension
            $extension = $this->getExtensionFromMimeType($oldPost->mime_type);

            // Construct the new filename with extension
            $baseFilename = pathinfo($oldPost->filename, PATHINFO_FILENAME);
            $newFilename = $baseFilename . '.' . $extension;
            $imagePath = 'files/posts/images/' . $newFilename; // Adjust path as needed

            // Convert hexadecimal string to binary
            $imageData = base64_decode($oldPost->file_data);

            try {
                

                // Save the image to storage (public disk)
                Storage::disk('public')->put($imagePath, $imageData);

                // Insert into new 'posts' table (if needed)
                // DB::table('posts')->insert([...]);

                $this->info('Converted and saved: ' . $imagePath);
            } catch (\Exception $e) {
                $this->error('Error processing image: ' . $e->getMessage());
            }
            break;
        }
    } */
}
