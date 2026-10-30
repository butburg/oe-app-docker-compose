<?php

use App\Models\Post;
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
        Schema::table('posts', function (Blueprint $table) {
            // Readable anchor of the post in the gallery (FEAT-014), e.g. #post-schoener-sonnenuntergang
            $table->string('slug')->nullable()->after('title');
        });

        // Only posts that were published once, drafts get their slug at the first publication.
        // Oldest post first, so it gets the plain slug and newer duplicates get the suffix
        DB::table('posts')->whereNotNull('published_at')->orderBy('published_at')->orderBy('id')->get(['id', 'title'])
            ->each(fn ($post) => DB::table('posts')->where('id', $post->id)->update([
                'slug' => Post::uniqueSlug($post->title, $post->id),
            ]));

        Schema::table('posts', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
