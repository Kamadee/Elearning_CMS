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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 255)->comment('transaction code');
            $table->unsignedBigInteger('order_id');
            $table->unsignedInteger('customer_id')->comment('customer ID');
            $table->bigInteger('amount')->comment('payment amount');
            $table->string('payment_method', 255);
            $table->tinyInteger('status')->comment('1: waiting confirm, 2: completed, 3: failed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
