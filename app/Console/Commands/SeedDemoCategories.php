<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DemoCategorySeederService;
use Illuminate\Console\Command;

class SeedDemoCategories extends Command
{
    protected $signature = 'demo:seed-categories';

    protected $description = 'Upsert the long-lived English course category taxonomy';

    public function __construct(private readonly DemoCategorySeederService $demoCategorySeederService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $summary = $this->demoCategorySeederService->seed();

        $this->table(
            ['Created', 'Already existed'],
            [[
                $summary['created'],
                $summary['existing'],
            ]],
        );
        $this->components->info('Course categories have been synchronized.');

        return self::SUCCESS;
    }
}
