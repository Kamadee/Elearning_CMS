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
        Schema::create('hot_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('content_type', 255)->comment('course, post, video...');
            $table->unsignedBigInteger('content_id')->comment('course_id, post_id...');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_hot_contents');
    }
};
