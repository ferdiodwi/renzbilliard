<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category IDs
        $makananCatId = Category::where('name', 'Makanan')->value('id');
        $minumanCatId = Category::where('name', 'Minuman')->value('id');
        $snackCatId = Category::where('name', 'Snack')->value('id');
        $gloveCatId = Category::where('name', 'Glove')->value('id');

        $products = [
            // Minuman
            ['name' => 'Kopi hitam', 'category_id' => $minumanCatId, 'price' => 5000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Kopi susu', 'category_id' => $minumanCatId, 'price' => 7000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Joshua', 'category_id' => $minumanCatId, 'price' => 7000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Kubisu', 'category_id' => $minumanCatId, 'price' => 7000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Matcha es/panas', 'category_id' => $minumanCatId, 'price' => 7000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Coklat es/panas', 'category_id' => $minumanCatId, 'price' => 7000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Milo es/panas', 'category_id' => $minumanCatId, 'price' => 7000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Kopi jahe', 'category_id' => $minumanCatId, 'price' => 7000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Jeruk es/panas', 'category_id' => $minumanCatId, 'price' => 7000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Cappucino es/panas', 'category_id' => $minumanCatId, 'price' => 7000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Teh', 'category_id' => $minumanCatId, 'price' => 5000, 'stock' => 50, 'is_available' => true],

            // Snack
            ['name' => 'Kentang goreng', 'category_id' => $snackCatId, 'price' => 8000, 'stock' => 50, 'is_available' => true],

            // Makanan
            ['name' => 'Mie goreng jumbo', 'category_id' => $makananCatId, 'price' => 8000, 'stock' => 50, 'is_available' => true],
            
            // Glove (unlimited stock - tidak berkurang saat dibeli)
            ['name' => 'Sewa Glove', 'category_id' => $gloveCatId, 'price' => 3000, 'stock' => null, 'is_available' => true],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
