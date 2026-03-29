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
        $items = [
            ['name' => 'Postre Esponjado De Limón', 'price' => 58075, 'stock' => 2, 'image' => '089d69aee82115be83cab3844b755ee0.jpg'],
            ['name' => 'Torta Angel Fresa', 'price' => 41400, 'stock' => 2, 'image' => '0eb1dc82b9d1b1aa1b5df85eb5d54847.jpg'],
            ['name' => 'Torta Chocoway', 'price' => 41400, 'stock' => 2, 'image' => '1a8d15972acbbaa5712e391ba5c41ab0.jpg'],
            ['name' => 'Choco Caramelo', 'price' => 41400, 'stock' => 2, 'image' => '1e92708d4c30a0c225cd6e2a8d6aac8b.jpg'],
            ['name' => 'Torta Caramelo Vainilla', 'price' => 41400, 'stock' => 2, 'image' => '403f8c1c16fc64bf8802b7763cc544ee.jpg'],
            ['name' => 'Postre Quesillo Tropical', 'price' => 70725, 'stock' => 2, 'image' => '4896776e7e9b9b9a0140a00fc30544fb.jpg'],
            ['name' => 'Postre Chocoflan', 'price' => 70725, 'stock' => 2, 'image' => '9ff7519385d770103d8b4bf6c53c7a37.jpg'],
            ['name' => 'Cheese Cake Frutos Rojos', 'price' => 70725, 'stock' => 2, 'image' => 'b120f62d192ee1fc85f06f2ce6b0d73a.jpg'],
        ];

        foreach ($items as $item) {
            Product::updateOrCreate(
                ['name' => $item['name']],
                [
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'image' => $item['image'],
                ]
            );
        }
    }
}
