<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;

final class BenchmarkSeedService
{
    private const BATCH_SIZE = 1000;

    /**
     * @return array{run_id: int, customers_created: int, courses_created: int, course_category_pivots_created: int, course_tag_pivots_created: int}
     */
    public function seed(int $customers, int $courses): array
    {
        $this->validateCounts($customers, $courses);

        $categoryIds = DB::table('post_categories')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        if ($categoryIds === []) {
            throw new LogicException('At least one active category is required before creating benchmark courses.');
        }

        $tagIds = DB::table('tags')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        return DB::transaction(function () use ($customers, $courses, $categoryIds, $tagIds): array {
            $token = (string) Str::uuid();
            $now = now();
            $runId = DB::table('benchmark_seed_runs')->insertGetId([
                'token' => $token,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->insertCustomers($token, $customers, $now);
            $customerIds = $this->benchmarkCustomerIds($token);
            $this->recordEntities($runId, 'customer', $customerIds, $now);

            $this->insertCourses($token, $courses, $now);
            $courseIds = $this->benchmarkCourseIds($token);
            $this->recordEntities($runId, 'course', $courseIds, $now);

            $courseCategoryPivotsCreated = $this->insertCourseCategoryPivots($courseIds, $categoryIds, $now);
            $courseTagPivotsCreated = $this->insertCourseTagPivots($courseIds, $tagIds, $now);

            return [
                'run_id' => (int) $runId,
                'customers_created' => count($customerIds),
                'courses_created' => count($courseIds),
                'course_category_pivots_created' => $courseCategoryPivotsCreated,
                'course_tag_pivots_created' => $courseTagPivotsCreated,
            ];
        });
    }

    /**
     * @return array{customers_deleted: int, courses_deleted: int, course_category_pivots_deleted: int, course_tag_pivots_deleted: int}
     */
    public function clear(int $runId): array
    {
        if (!DB::table('benchmark_seed_runs')->where('id', $runId)->exists()) {
            throw new InvalidArgumentException("Benchmark seed run {$runId} does not exist.");
        }

        return DB::transaction(function () use ($runId): array {
            $customerIds = $this->recordedEntityIds($runId, 'customer');
            $courseIds = $this->recordedEntityIds($runId, 'course');

            $courseCategoryPivotsDeleted = $this->deleteByIds('course_category_pivot', 'course_id', $courseIds);
            $courseTagPivotsDeleted = $this->deleteByIds('course_tags', 'course_id', $courseIds);
            $coursesDeleted = $this->deleteByIds('courses', 'id', $courseIds);
            $customersDeleted = $this->deleteByIds('customers', 'id', $customerIds);

            DB::table('benchmark_seed_records')->where('benchmark_seed_run_id', $runId)->delete();
            DB::table('benchmark_seed_runs')->where('id', $runId)->delete();

            return [
                'customers_deleted' => $customersDeleted,
                'courses_deleted' => $coursesDeleted,
                'course_category_pivots_deleted' => $courseCategoryPivotsDeleted,
                'course_tag_pivots_deleted' => $courseTagPivotsDeleted,
            ];
        });
    }

    private function validateCounts(int $customers, int $courses): void
    {
        if ($customers < 1 || $customers > 100_000) {
            throw new InvalidArgumentException('The customer count must be between 1 and 100000.');
        }

        if ($courses < 1 || $courses > 50_000) {
            throw new InvalidArgumentException('The course count must be between 1 and 50000.');
        }
    }

    private function insertCustomers(string $token, int $count, DateTimeInterface $now): void
    {
        $password = Hash::make(Str::random(40));

        foreach (range(1, $count) as $index) {
            $rows[] = [
                'first_name' => 'Benchmark',
                'last_name' => "Customer {$index}",
                'email' => "benchmark+{$token}-{$index}@example.test",
                'phone' => sprintf('090%08d', $index),
                'password' => $password,
                'rank' => 'benchmark',
                'money' => 0,
                'status' => 2,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) === self::BATCH_SIZE || $index === $count) {
                DB::table('customers')->insert($rows);
                $rows = [];
            }
        }
    }

    private function insertCourses(string $token, int $count, DateTimeInterface $now): void
    {
        foreach (range(1, $count) as $index) {
            $rows[] = [
                'title' => "[Benchmark:{$token}] Course {$index}",
                'description' => 'Synthetic course record generated exclusively for local index benchmarking.',
                'author' => 'Benchmark Seeder',
                'course_duration' => '1 hour',
                'status' => 1,
                'original_price' => 100000,
                'sale_off_price' => 90000,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) === self::BATCH_SIZE || $index === $count) {
                DB::table('courses')->insert($rows);
                $rows = [];
            }
        }
    }

    /**
     * @param  list<int>  $courseIds
     * @param  list<int>  $categoryIds
     */
    private function insertCourseCategoryPivots(array $courseIds, array $categoryIds, DateTimeInterface $now): int
    {
        $rows = [];

        foreach ($courseIds as $index => $courseId) {
            $rows[] = [
                'course_id' => $courseId,
                'post_category_id' => $categoryIds[$index % count($categoryIds)],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) === self::BATCH_SIZE || $index === array_key_last($courseIds)) {
                DB::table('course_category_pivot')->insert($rows);
                $rows = [];
            }
        }

        return count($courseIds);
    }

    /**
     * @param  list<int>  $courseIds
     * @param  list<int>  $tagIds
     */
    private function insertCourseTagPivots(array $courseIds, array $tagIds, DateTimeInterface $now): int
    {
        if ($tagIds === []) {
            return 0;
        }

        $rows = [];

        foreach ($courseIds as $index => $courseId) {
            $rows[] = [
                'course_id' => $courseId,
                'tag_id' => $tagIds[$index % count($tagIds)],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) === self::BATCH_SIZE || $index === array_key_last($courseIds)) {
                DB::table('course_tags')->insert($rows);
                $rows = [];
            }
        }

        return count($courseIds);
    }

    /**
     * @param  list<int>  $entityIds
     */
    private function recordEntities(int $runId, string $entityType, array $entityIds, DateTimeInterface $now): void
    {
        foreach (array_chunk($entityIds, self::BATCH_SIZE) as $entityIdBatch) {
            DB::table('benchmark_seed_records')->insert(array_map(
                static fn (int $entityId): array => [
                    'benchmark_seed_run_id' => $runId,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                $entityIdBatch,
            ));
        }
    }

    /**
     * @return list<int>
     */
    private function benchmarkCustomerIds(string $token): array
    {
        return DB::table('customers')
            ->where('email', 'like', "benchmark+{$token}-%@example.test")
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    private function benchmarkCourseIds(string $token): array
    {
        return DB::table('courses')
            ->where('title', 'like', "[Benchmark:{$token}]%")
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    private function recordedEntityIds(int $runId, string $entityType): array
    {
        return DB::table('benchmark_seed_records')
            ->where('benchmark_seed_run_id', $runId)
            ->where('entity_type', $entityType)
            ->orderBy('entity_id')
            ->pluck('entity_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @param  list<int>  $ids
     */
    private function deleteByIds(string $table, string $column, array $ids): int
    {
        $deleted = 0;

        foreach (array_chunk($ids, self::BATCH_SIZE) as $idBatch) {
            $deleted += DB::table($table)->whereIn($column, $idBatch)->delete();
        }

        return $deleted;
    }
}
