<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan',
                'description' => 'Produk makanan',
                'color' => '#f59e0b', // amber
            ],
            [
                'name' => 'Minuman',
                'description' => 'Produk minuman',
                'color' => '#3b82f6', // blue
            ],
            [
                'name' => 'Snack',
                'description' => 'Produk snack dan cemilan',
                'color' => '#10b981', // green
            ],
            [
                'name' => 'Glove',
                'description' => 'Sewa Glove',
                'color' => '#8b5cf6', // purple
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
