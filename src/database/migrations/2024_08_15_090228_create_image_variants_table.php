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
        Schema::create('image_variants', function (Blueprint $table) {
            $table->id();
            // Creates an unsigned big integer 'image_id' column.
            // Adds a foreign key constraint on 'image_id', linking it to the 'id' column of the 'images' table.
            // If the related image is deleted, any rows in this table with that 'image_id' will also be deleted (cascade delete).
            $table->foreignId('image_id')->constrained('images')->onDelete('cascade');
            // Stores the path to the image variant file on the server.
            $table->string('path');
            // Represents the maximum dimension (either width or height, whichever is larger) of the image variant.
            $table->string('size_type', 3); // e.g. max-allowed, desktop, mobile
            // Stores the quality of the image variant (e.g., for JPEG compression).
            $table->integer('quality');
            // Stores the width of the image variant. Nullable because not all image variants may have an explicit width.
            $table->integer('width')->nullable();
            // Stores the height of the image variant. Nullable because not all image variants may have an explicit height.
            $table->integer('height')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_variants');
    }
};
