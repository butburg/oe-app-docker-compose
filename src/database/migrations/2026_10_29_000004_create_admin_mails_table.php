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
        // Log of the mails admins sent to users (FEAT-017)
        Schema::create('admin_mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject');
            $table->text('body');
            // verified | everyone | user
            $table->string('group');
            $table->string('recipient_email')->nullable();
            $table->unsignedInteger('recipient_count');
            // from the confirm page, a second submit of the same page does not send again
            $table->uuid('send_token')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_mails');
    }
};
