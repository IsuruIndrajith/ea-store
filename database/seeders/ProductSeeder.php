<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryNames = [
            'forex-ea'     => 'Forex EA',
            'gold-ea'      => 'Gold EA',
            'low-drawdown' => 'Low Drawdown',
            'indicators'   => 'Indicators',
            'source-code'  => 'Source Code',
        ];

        foreach ($categoryNames as $slug => $name) {
            Category::updateOrCreate(['slug' => $slug], ['name' => $name]);
        }
    }
}
