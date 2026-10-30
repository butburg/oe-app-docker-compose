<?php

use App\Models\ImageVariant;
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
        Schema::table('image_variants', function (Blueprint $table) {
            // Random id in the image link instead of the counting id, so links do not tell how many images exist (FEAT-011)
            $table->string('public_id', 16)->nullable()->after('id');
        });

        DB::table('image_variants')->pluck('id')
            ->each(fn ($id) => DB::table('image_variants')->where('id', $id)->update(['public_id' => ImageVariant::newPublicId()]));

        Schema::table('image_variants', function (Blueprint $table) {
            $table->unique('public_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_variants', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });
    }
};
