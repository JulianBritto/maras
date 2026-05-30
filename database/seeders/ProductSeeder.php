<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Producto 1 - Limón Esponjado', 'price' => 20000, 'stock' => 10, 'image' => '089d69aee82115be83cab3844b755ee0.jpg'],
            ['name' => 'Producto 2 - Torta Fresa', 'price' => 10000, 'stock' => 10, 'image' => '0eb1dc82b9d1b1aa1b5df85eb5d54847.jpg'],
            ['name' => 'Producto 3 - Torta Choco', 'price' => 20000, 'stock' => 10, 'image' => '1a8d15972acbbaa5712e391ba5c41ab0.jpg'],
            ['name' => 'Producto 4 - Choco Caramelo', 'price' => 10000, 'stock' => 10, 'image' => '1e92708d4c30a0c225cd6e2a8d6aac8b.jpg'],
            ['name' => 'Producto 5 - Caramelo Vainilla', 'price' => 20000, 'stock' => 10, 'image' => '403f8c1c16fc64bf8802b7763cc544ee.jpg'],
            ['name' => 'Producto 6 - Quesillo Tropical', 'price' => 10000, 'stock' => 10, 'image' => '4896776e7e9b9b9a0140a00fc30544fb.jpg'],
            ['name' => 'Producto 7 - Chocoflan', 'price' => 20000, 'stock' => 10, 'image' => '9ff7519385d770103d8b4bf6c53c7a37.jpg'],
            ['name' => 'Producto 8 - Cheese Cake Frutos Rojos', 'price' => 10000, 'stock' => 10, 'image' => 'b120f62d192ee1fc85f06f2ce6b0d73a.jpg'],
        ];

        foreach ($items as $item) {
            Product::updateOrCreate(
                ['image' => $item['image']],
                [
                    'name' => $item['name'],
                    'price' => (int) $item['price'],
                    'stock' => (int) $item['stock'],
                ]
            );
        }
    }
}
