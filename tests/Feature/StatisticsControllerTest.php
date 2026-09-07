<?php

namespace Tests\Feature;

use App\Http\Controllers\StatisticsController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StatisticsControllerTest extends TestCase
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

        Schema::create('courses', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->text('thumbnail')->nullable();
            $table->text('author')->nullable();
            $table->integer('original_price')->nullable();
            $table->integer('sale_off_price')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('code');
            $table->integer('amount');
            $table->integer('customer_id');
            $table->tinyInteger('status');
            $table->timestamps();
        });
        Schema::create('order_items', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('order_id');
            $table->integer('course_id');
            $table->string('course_title');
            $table->integer('quantity');
            $table->integer('price');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('courses');
        DB::disconnect('sqlite');

        parent::tearDown();
    }

    public function test_top_purchased_courses_counts_only_completed_order_items_and_returns_top_ten(): void
    {
        $courseIds = [];
        for ($index = 1; $index <= 12; $index++) {
            $courseIds[$index] = DB::table('courses')->insertGetId([
                'title' => "Course {$index}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $completedOrderId = DB::table('orders')->insertGetId([
            'code' => 'STAT-COMPLETED',
            'amount' => 1000,
            'customer_id' => 1,
            'status' => config('constants.order_status.completed'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('order_items')->insert([
            'order_id' => $completedOrderId,
            'course_id' => $courseIds[1],
            'course_title' => 'Course 1',
            'quantity' => 2,
            'price' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pendingOrderId = DB::table('orders')->insertGetId([
            'code' => 'STAT-PENDING',
            'amount' => 1000,
            'customer_id' => 1,
            'status' => config('constants.order_status.processing'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('order_items')->insert([
            'order_id' => $pendingOrderId,
            'course_id' => $courseIds[2],
            'course_title' => 'Course 2',
            'quantity' => 100,
            'price' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        for ($index = 3; $index <= 12; $index++) {
            $orderId = DB::table('orders')->insertGetId([
                'code' => "STAT-{$index}",
                'amount' => 1000,
                'customer_id' => 1,
                'status' => config('constants.order_status.completed'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('order_items')->insert([
                'order_id' => $orderId,
                'course_id' => $courseIds[$index],
                'course_title' => "Course {$index}",
                'quantity' => 1,
                'price' => 1000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $result = app(StatisticsController::class)->topPurchasedCourses();

        $this->assertCount(10, $result);
        $this->assertSame($courseIds[1], $result->first()->id);
        $this->assertSame(2, (int) $result->first()->items_count);
        $this->assertNotContains($courseIds[2], $result->pluck('id')->all());
        $this->assertNotContains($courseIds[12], $result->pluck('id')->all());
    }
}
