<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        User::truncate();
        Category::truncate();
        Product::truncate();
        Transaction::truncate();
        DB::table('category_product')->truncate();

        User::flushEventListeners();
        Category::flushEventListeners();
        Product::flushEventListeners();
        Transaction::flushEventListeners();

        $cantidadUsuarios = 1000;
        $cantidadCategorias = 30;
        $cantidadProductos = 1000;
        $cantidadTransacciones = 1000;

        User::factory($cantidadUsuarios)->create();
        Category::factory($cantidadCategorias)->create();

        Product::factory($cantidadProductos)->create()->each(function ($producto) {
            $categorias = Category::all()->random(mt_rand(1, 5))->pluck('id');
            $producto->categories()->attach($categorias);
        });


        Transaction::factory($cantidadTransacciones)->create();
    }
}
