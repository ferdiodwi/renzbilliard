<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Minuman
            [
                'name' => 'Air Mineral',
                'category' => 'minuman',
                'price' => 5000,
                'stock' => 100,
                'is_available' => true,
            ],
            [
                'name' => 'Teh Botol',
                'category' => 'minuman',
                'price' => 7000,
                'stock' => 50,
                'is_available' => true,
            ],
            [
                'name' => 'Coca Cola',
                'category' => 'minuman',
                'price' => 10000,
                'stock' => 50,
                'is_available' => true,
            ],
            [
                'name' => 'Sprite',
                'category' => 'minuman',
                'price' => 10000,
                'stock' => 50,
                'is_available' => true,
            ],
            [
                'name' => 'Fanta',
                'category' => 'minuman',
                'price' => 10000,
                'stock' => 50,
                'is_available' => true,
            ],
            [
                'name' => 'Kopi Hitam',
                'category' => 'minuman',
                'price' => 8000,
                'stock' => 30,
                'is_available' => true,
            ],
            [
                'name' => 'Kopi Susu',
                'category' => 'minuman',
                'price' => 10000,
                'stock' => 30,
                'is_available' => true,
            ],
            [
                'name' => 'Cappuccino',
                'category' => 'minuman',
                'price' => 12000,
                'stock' => 30,
                'is_available' => true,
            ],
            [
                'name' => 'Es Teh Manis',
                'category' => 'minuman',
                'price' => 6000,
                'stock' => 50,
                'is_available' => true,
            ],
            [
                'name' => 'Es Jeruk',
                'category' => 'minuman',
                'price' => 8000,
                'stock' => 40,
                'is_available' => true,
            ],
            [
                'name' => 'Jus Alpukat',
                'category' => 'minuman',
                'price' => 15000,
                'stock' => 20,
                'is_available' => true,
            ],
            [
                'name' => 'Jus Mangga',
                'category' => 'minuman',
                'price' => 15000,
                'stock' => 20,
                'is_available' => true,
            ],

            // Makanan Ringan / Snack
            [
                'name' => 'Keripik Kentang',
                'category' => 'snack',
                'price' => 12000,
                'stock' => 40,
                'is_available' => true,
            ],
            [
                'name' => 'Kacang Atom',
                'category' => 'snack',
                'price' => 10000,
                'stock' => 40,
                'is_available' => true,
            ],
            [
                'name' => 'Popcorn',
                'category' => 'snack',
                'price' => 8000,
                'stock' => 30,
                'is_available' => true,
            ],
            [
                'name' => 'Nachos',
                'category' => 'snack',
                'price' => 15000,
                'stock' => 25,
                'is_available' => true,
            ],
            [
                'name' => 'French Fries',
                'category' => 'snack',
                'price' => 18000,
                'stock' => 30,
                'is_available' => true,
            ],
            [
                'name' => 'Onion Rings',
                'category' => 'snack',
                'price' => 20000,
                'stock' => 25,
                'is_available' => true,
            ],

            // Makanan Berat
            [
                'name' => 'Nasi Goreng',
                'category' => 'makanan',
                'price' => 25000,
                'stock' => 20,
                'is_available' => true,
            ],
            [
                'name' => 'Mie Goreng',
                'category' => 'makanan',
                'price' => 22000,
                'stock' => 20,
                'is_available' => true,
            ],
            [
                'name' => 'Nasi Ayam Geprek',
                'category' => 'makanan',
                'price' => 28000,
                'stock' => 15,
                'is_available' => true,
            ],
            [
                'name' => 'Nasi Ayam Penyet',
                'category' => 'makanan',
                'price' => 28000,
                'stock' => 15,
                'is_available' => true,
            ],
            [
                'name' => 'Burger Beef',
                'category' => 'makanan',
                'price' => 30000,
                'stock' => 15,
                'is_available' => true,
            ],
            [
                'name' => 'Burger Chicken',
                'category' => 'makanan',
                'price' => 28000,
                'stock' => 15,
                'is_available' => true,
            ],
            [
                'name' => 'Sandwich Club',
                'category' => 'makanan',
                'price' => 25000,
                'stock' => 15,
                'is_available' => true,
            ],
            [
                'name' => 'Pizza Slice',
                'category' => 'makanan',
                'price' => 20000,
                'stock' => 20,
                'is_available' => true,
            ],

            // Dessert
            [
                'name' => 'Es Krim Vanila',
                'category' => 'snack',
                'price' => 12000,
                'stock' => 25,
                'is_available' => true,
            ],
            [
                'name' => 'Es Krim Cokelat',
                'category' => 'snack',
                'price' => 12000,
                'stock' => 25,
                'is_available' => true,
            ],
            [
                'name' => 'Brownies',
                'category' => 'snack',
                'price' => 15000,
                'stock' => 20,
                'is_available' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
