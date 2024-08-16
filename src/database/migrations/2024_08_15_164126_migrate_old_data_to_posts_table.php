<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    /**
     * Convert MIME type to file extension
     *
     * @param string $mimeType
     * @return string
     */
    protected function getExtensionFromMimeType($mimeType)
    {
        $mimeTypes = [
            'image/jpeg' => 'jpeg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
            'image/webp' => 'webp',
            // Add more MIME types and their extensions as needed
        ];
        return isset($mimeTypes[$mimeType]) ? $mimeTypes[$mimeType] : '';
    }

    public function up()
    {
        // Fetch data from the old SQLite table
        $oldPosts = DB::connection('old_sqlite')
            ->table('images')
            ->select('filename', 'mime_type', 'user', 'date', 'file_data')
            ->get();

        foreach ($oldPosts as $oldPost) {
            // Get the correct file extension
            $extension = $this->getExtensionFromMimeType($oldPost->mime_type);

            // Construct the new filename with extension
            $newFilename = pathinfo($oldPost->filename, PATHINFO_FILENAME);
            $imagePath = 'files/posts/images_legacy/' . $newFilename . '.' . $extension; // Adjust path as needed

            // Insert into new 'posts' table
            DB::table('posts')->insert([
                'title' => $newFilename,
                // TODO 'image_file' => $imagePath, removed for image variants
                // make it image_id
                'is_published' => true, // Assuming all are published
                'is_sensitive' => false, // Adjust as needed
                'created_at' => $oldPost->date,
                'updated_at' => now(),
                'user_id' => NULL, // Adjust as needed
                'username' => $oldPost->user,
            ]);
        }
    }

    public function down()
    {
        // If needed, implement rollback logic
    }
};
