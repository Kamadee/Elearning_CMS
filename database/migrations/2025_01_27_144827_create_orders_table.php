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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 255)->comment('order code');
            $table->bigInteger('amount')->comment('total order amount');
            $table->unsignedInteger('customer_id')->comment('customer ID');
            $table->string('payment_method', 255)->nullable();
            $table->timestamp('payment_time')->nullable();
            $table->tinyInteger('status')->comment('1: placed, 2: processing, 3: completed, 4: cancelled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
