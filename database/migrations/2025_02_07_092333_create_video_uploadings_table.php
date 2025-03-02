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
        Schema::create('video_uploadings', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id');
            $table->integer('video_id')->nullable();
            $table->integer('vimeo_id')->nullable();
            $table->text('file_path')->nullable();
            $table->integer('job_id')->nullable(true);
            $table->integer('job_status')->default(1);
            $table->text('thumbnail_id')->nullable(true);
            $table->text('error_log')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_uploadings');
    }
};
