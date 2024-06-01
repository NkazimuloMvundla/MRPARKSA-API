<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReviewAspect;

class ReviewAspectSeeder extends Seeder
{
    public function run()
    {
        $aspects = [
            'Safety',
            'Ease of Finding',
            'Size',
        ];

        foreach ($aspects as $aspect) {
            ReviewAspect::create(['name' => $aspect]);
        }
    }
}
