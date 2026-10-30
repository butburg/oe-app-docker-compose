<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Bundled mail when other users publish new images (FEAT-019), on for new and existing users
            $table->boolean('notify_new_images')->default(true);
        });

        Schema::table('posts', function (Blueprint $table) {
            // Set once the new images mail went out, publishing the post again does not notify twice
            $table->timestamp('notified_at')->nullable();
        });

        // Posts that are already published count as announced, they never get a mail
        DB::table('posts')->where('is_published', true)->update(['notified_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('notified_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notify_new_images');
        });
    }
};
