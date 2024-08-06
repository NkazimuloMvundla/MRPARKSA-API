<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReviewAspect;

class ReviewAspectSeeder extends Seeder
{
    public function run()
    {
        $aspects = [
            'safety',
            'ease_of_finding',
            'size',
        ];

        foreach ($aspects as $aspect) {
            ReviewAspect::create(['name' => $aspect]);
        }
    }
}
