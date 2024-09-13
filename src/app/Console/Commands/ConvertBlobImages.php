<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Not needed anymore! Since images are from blob now files.
 */
class ConvertBlobImages extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:convert-blob-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert BLOB data to images and store them in storage as files.';

    /**
     * Convert MIME type to file extension
     *
     * @param string $mimeType
     * @return string
     */
    protected function getExtensionFromMimeType($mimeType) {
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
    public function handle() {

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
}
