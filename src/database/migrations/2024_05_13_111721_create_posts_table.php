<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the 'posts' table with specified schema
        Schema::create('posts', function (Blueprint $table) {
            // Primary key 'id' field auto-incremented
            $table->id();

            // 'title' column to store the image title
            $table->string('title');

            // 'image_file' column to store information about attached files
            $table->string('image_file');

            // to store, if the post should be visible and published in gallery or only for the uploader
            $table->boolean('is_published')->default(false);

            // to store, if the impage from post contains faces or other content, that sould only available to logged in users
            $table->boolean('is_sensitive')->default(false);

            // 'created_at' and 'updated_at' columns to store timestamps of creation and updates
            $table->timestamps();

            // Add user_id column and make it nullable to handle cases where the user is deleted
            $table->unsignedBigInteger('user_id')->nullable();
            
            // Store the username to keep track of the uploader even after user deletion
            $table->string('username');

            // Define the foreign key constraint without cascading on delete
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
