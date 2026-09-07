<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benchmark_seed_runs', function (Blueprint $table): void {
            $table->increments('id');
            $table->uuid('token')->unique();
            $table->timestamps();
        });

        Schema::create('benchmark_seed_records', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('benchmark_seed_run_id');
            $table->string('entity_type', 32);
            $table->unsignedInteger('entity_id');
            $table->timestamps();

            $table->foreign('benchmark_seed_run_id')
                ->references('id')
                ->on('benchmark_seed_runs')
                ->cascadeOnDelete();
            $table->unique(
                ['benchmark_seed_run_id', 'entity_type', 'entity_id'],
                'benchmark_seed_records_run_entity_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benchmark_seed_records');
        Schema::dropIfExists('benchmark_seed_runs');
    }
};
