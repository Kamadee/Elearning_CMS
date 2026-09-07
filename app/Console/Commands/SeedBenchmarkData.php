<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BenchmarkSeedService;
use Illuminate\Console\Command;
use InvalidArgumentException;
use LogicException;

class SeedBenchmarkData extends Command
{
    protected $signature = 'benchmark:seed
        {--customers=50000 : Number of synthetic customers to create (1-100000)}
        {--courses=20000 : Number of synthetic courses to create (1-50000)}
        {--force : Confirm the high-volume operation without an interactive prompt}';

    protected $description = 'Create a tracked synthetic dataset for local database benchmark tests';

    public function __construct(private readonly BenchmarkSeedService $benchmarkSeedService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->laravel->environment('production')) {
            $this->components->error('Benchmark seeding is disabled in production.');

            return self::FAILURE;
        }

        $customers = (int) $this->option('customers');
        $courses = (int) $this->option('courses');

        if (!$this->option('force') && !$this->confirm("Create {$customers} synthetic customers and {$courses} synthetic courses?")) {
            $this->components->info('Operation cancelled.');

            return self::SUCCESS;
        }

        try {
            $summary = $this->benchmarkSeedService->seed($customers, $courses);
        } catch (InvalidArgumentException|LogicException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Run ID', 'Customers', 'Courses', 'Category pivots', 'Tag pivots'],
            [[
                $summary['run_id'],
                $summary['customers_created'],
                $summary['courses_created'],
                $summary['course_category_pivots_created'],
                $summary['course_tag_pivots_created'],
            ]],
        );
        $this->components->info("Use benchmark:clear {$summary['run_id']} to remove only this dataset.");

        return self::SUCCESS;
    }
}
