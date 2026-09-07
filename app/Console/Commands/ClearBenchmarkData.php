<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BenchmarkSeedService;
use Illuminate\Console\Command;
use InvalidArgumentException;

class ClearBenchmarkData extends Command
{
    protected $signature = 'benchmark:clear
        {run-id : The benchmark run ID returned by benchmark:seed}
        {--force : Confirm the deletion without an interactive prompt}';

    protected $description = 'Remove only the synthetic data owned by one benchmark seed run';

    public function __construct(private readonly BenchmarkSeedService $benchmarkSeedService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->laravel->environment('production')) {
            $this->components->error('Benchmark cleanup is disabled in production.');

            return self::FAILURE;
        }

        $runId = (int) $this->argument('run-id');

        if (!$this->option('force') && !$this->confirm("Permanently remove synthetic data from benchmark run {$runId}?")) {
            $this->components->info('Operation cancelled.');

            return self::SUCCESS;
        }

        try {
            $summary = $this->benchmarkSeedService->clear($runId);
        } catch (InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Customers', 'Courses', 'Category pivots', 'Tag pivots'],
            [[
                $summary['customers_deleted'],
                $summary['courses_deleted'],
                $summary['course_category_pivots_deleted'],
                $summary['course_tag_pivots_deleted'],
            ]],
        );
        $this->components->info('Benchmark dataset removed.');

        return self::SUCCESS;
    }
}
