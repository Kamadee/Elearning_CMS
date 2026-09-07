<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BenchmarkSeedServiceTest extends TestCase
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

        Schema::create('customers', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('rank')->nullable();
            $table->double('money')->nullable();
            $table->tinyInteger('status')->default(2);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
        Schema::create('courses', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('author')->nullable();
            $table->text('course_duration')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->double('original_price')->default(0);
            $table->double('sale_off_price')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
        Schema::create('post_categories', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('category_name')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
        Schema::create('tags', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('tag_name')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
        Schema::create('course_category_pivot', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('post_category_id');
            $table->timestamps();
        });
        Schema::create('course_tags', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('tag_id');
            $table->unsignedInteger('course_id');
            $table->timestamps();
        });
        Schema::create('benchmark_seed_runs', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('token')->unique();
            $table->timestamps();
        });
        Schema::create('benchmark_seed_records', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('benchmark_seed_run_id');
            $table->string('entity_type');
            $table->unsignedInteger('entity_id');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('benchmark_seed_records');
        Schema::dropIfExists('benchmark_seed_runs');
        Schema::dropIfExists('course_tags');
        Schema::dropIfExists('course_category_pivot');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('post_categories');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('customers');
        DB::disconnect('sqlite');

        parent::tearDown();
    }

    public function test_it_clears_only_the_customers_courses_and_pivots_owned_by_one_benchmark_run(): void
    {
        $existingCustomerId = DB::table('customers')->insertGetId([
            'email' => 'existing@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $existingCourseId = DB::table('courses')->insertGetId([
            'title' => 'Existing course',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $categoryId = DB::table('post_categories')->insertGetId([
            'category_name' => 'Development',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tagId = DB::table('tags')->insertGetId([
            'tag_name' => 'php',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('course_category_pivot')->insert([
            'course_id' => $existingCourseId,
            'post_category_id' => $categoryId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app('App\\Services\\BenchmarkSeedService');
        $seeded = $service->seed(customers: 2, courses: 3);

        $this->assertSame(2, $seeded['customers_created']);
        $this->assertSame(3, $seeded['courses_created']);
        $this->assertDatabaseCount('customers', 3);
        $this->assertDatabaseCount('courses', 4);
        $this->assertDatabaseCount('course_category_pivot', 4);
        $this->assertDatabaseCount('course_tags', 3);
        $this->assertDatabaseCount('benchmark_seed_records', 5);

        $cleared = $service->clear($seeded['run_id']);

        $this->assertSame(2, $cleared['customers_deleted']);
        $this->assertSame(3, $cleared['courses_deleted']);
        $this->assertDatabaseHas('customers', ['id' => $existingCustomerId]);
        $this->assertDatabaseHas('courses', ['id' => $existingCourseId]);
        $this->assertDatabaseCount('course_category_pivot', 1);
        $this->assertDatabaseCount('course_tags', 0);
        $this->assertDatabaseCount('benchmark_seed_runs', 0);
        $this->assertDatabaseCount('benchmark_seed_records', 0);
    }
}
