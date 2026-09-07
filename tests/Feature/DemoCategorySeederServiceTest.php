<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DemoCategorySeederServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('post_categories', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('category_name')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('post_categories');
        DB::disconnect('sqlite');

        parent::tearDown();
    }

    public function test_it_upserts_the_fifteen_long_lived_english_categories_without_duplicates(): void
    {
        DB::table('post_categories')->insert([
            'category_name' => 'Development',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app('App\\Services\\DemoCategorySeederService');

        $firstRun = $service->seed();

        $this->assertSame(14, $firstRun['created']);
        $this->assertSame(1, $firstRun['existing']);
        $this->assertDatabaseCount('post_categories', 15);
        $this->assertDatabaseHas('post_categories', ['category_name' => 'Data Science & AI']);
        $this->assertDatabaseHas('post_categories', ['category_name' => 'Photography & Video']);

        $secondRun = $service->seed();

        $this->assertSame(0, $secondRun['created']);
        $this->assertSame(15, $secondRun['existing']);
        $this->assertDatabaseCount('post_categories', 15);
    }
}
