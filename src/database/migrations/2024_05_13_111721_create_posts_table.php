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

            // 'info_file' column to store information about attached files (nullable)
            $table->string('info_file')->nullable();

            // to store, if the post should be visible and published in gallery or only for the uploader
            $table->boolean('is_published')->default(false);

            // to store, if the impage from post contains faces or other content, that sould only available to logged in users
            $table->boolean('is_sensitive')->default(false);

            // 'created_at' and 'updated_at' columns to store timestamps of creation and updates
            $table->timestamps();
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
