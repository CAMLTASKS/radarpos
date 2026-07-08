<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@heladeria.com',
            'password' => bcrypt('password'),
        ]);

        $vainilla = \App\Models\Ingredient::create(['name' => 'Base Vainilla', 'unit' => 'L', 'stock' => 50]);
        $fresa = \App\Models\Ingredient::create(['name' => 'Base Fresa', 'unit' => 'L', 'stock' => 30]);
        $cono = \App\Models\Ingredient::create(['name' => 'Conos Galleta', 'unit' => 'Unidad', 'stock' => 100]);
        $chocolate = \App\Models\Ingredient::create(['name' => 'Sirope Chocolate', 'unit' => 'ml', 'stock' => 2000]);

        $p1 = \App\Models\Product::create(['name' => 'Helado de Fresa', 'price' => 2.50, 'category' => 'Cono', 'image' => '🍦']);
        $p1->ingredients()->attach($fresa->id, ['quantity' => 0.1]); // 100ml
        $p1->ingredients()->attach($cono->id, ['quantity' => 1]);

        $p2 = \App\Models\Product::create(['name' => 'Helado de Vainilla', 'price' => 2.50, 'category' => 'Cono', 'image' => '🍦']);
        $p2->ingredients()->attach($vainilla->id, ['quantity' => 0.1]);
        $p2->ingredients()->attach($cono->id, ['quantity' => 1]);

        $p3 = \App\Models\Product::create(['name' => 'Sundae Chocolate', 'price' => 4.00, 'category' => 'Copa', 'image' => '🍨']);
        $p3->ingredients()->attach($vainilla->id, ['quantity' => 0.2]);
        $p3->ingredients()->attach($chocolate->id, ['quantity' => 50]); // 50ml
    }
}
