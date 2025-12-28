<?php

namespace Database\Seeders;

use App\Models\Rate;
use App\Models\TableBilliard;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
        ]);

        // Create sample tables (10 tables)
        for ($i = 1; $i <= 10; $i++) {
            TableBilliard::create([
                'table_number' => 'M' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'status' => 'available',
            ]);
        }

        // Create sample rates
        Rate::create([
            'name' => 'Reguler',
            'price_per_hour' => 30000,
        ]);

        Rate::create([
            'name' => 'Premium',
            'price_per_hour' => 50000,
        ]);

        Rate::create([
            'name' => 'VIP',
            'price_per_hour' => 75000,
        ]);

        Rate::create([
            'name' => 'Promo Malam',
            'price_per_hour' => 25000,
        ]);
    }
}
