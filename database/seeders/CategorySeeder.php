<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Kinds of cuisine a restaurant can belong to.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Cocina de huerta', 'Vegano', 'Vegetariano', 'Bowls y ensaladas', 'Panadería y café', 'Jugos y licuados'] as $name) {
            Category::create(['name' => $name, 'slug' => Str::slug($name)]);
        }
    }
}
