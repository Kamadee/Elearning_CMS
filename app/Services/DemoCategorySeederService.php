<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class DemoCategorySeederService
{
    /**
     * @var list<string>
     */
    private const CATEGORY_NAMES = [
        'Development',
        'Data Science & AI',
        'IT & Software',
        'Design',
        'Business',
        'Finance & Accounting',
        'Marketing',
        'Personal Development',
        'Health & Fitness',
        'Lifestyle',
        'Photography & Video',
        'Music',
        'Teaching & Academics',
        'Language Learning',
        'Art & Creativity',
    ];

    /**
     * @return array{created: int, existing: int}
     */
    public function seed(): array
    {
        return DB::transaction(function (): array {
            $summary = ['created' => 0, 'existing' => 0];
            $now = now();

            foreach (self::CATEGORY_NAMES as $categoryName) {
                $exists = DB::table('post_categories')
                    ->where('category_name', $categoryName)
                    ->exists();

                if ($exists) {
                    $summary['existing']++;

                    continue;
                }

                DB::table('post_categories')->insert([
                    'category_name' => $categoryName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $summary['created']++;
            }

            return $summary;
        });
    }
}
