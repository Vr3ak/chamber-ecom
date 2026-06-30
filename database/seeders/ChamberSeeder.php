<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use App\Models\Review;
use App\Models\Size;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ChamberSeeder extends Seeder
{
    public function run(): void
    {
        $nike   = Brand::firstOrCreate(['name' => 'Nike']);
        $adidas = Brand::firstOrCreate(['name' => 'Adidas']);
        $puma   = Brand::firstOrCreate(['name' => 'Puma']);

        $red  = Color::firstOrCreate(['name' => 'Red'],  ['hex' => '#E11D1D']);
        $blue = Color::firstOrCreate(['name' => 'Blue'], ['hex' => '#1D4ED8']);

        $s42 = Size::firstOrCreate(['label' => '42'], ['cm' => 26.5]);
        $s43 = Size::firstOrCreate(['label' => '43'], ['cm' => 27.5]);

        $alice = User::firstOrCreate(
            ['email' => 'alice@example.com'],
            ['name' => 'Alice', 'password' => Hash::make('password')]
        );
        $bob = User::firstOrCreate(
            ['email' => 'bob@example.com'],
            ['name' => 'Bob', 'password' => Hash::make('password')]
        );

        $airmax = Product::firstOrCreate(
            ['slug' => 'air-max-90'],
            [
                'name'        => 'Air Max 90',
                'brand_id'    => $nike->id,
                'base_price'  => 120.00,
                'description' => 'The Air Max 90 stays true to its roots with the iconic '
                    .'Waffle outsole, stitched overlays and visible Air cushioning.',
                'size_guide'  => 'Fits true to size. If you are between sizes, size up. '
                    .'Measure your foot in cm and match it to the size chart.',
            ]
        );
        $this->variants($airmax, [
            [$red,  $s42, 'AM90-RED-42', 12],
            [$red,  $s43, 'AM90-RED-43', 8],
            [$blue, $s42, 'AM90-BLU-42', 5],
            [$blue, $s43, 'AM90-BLU-43', 9],
        ]);
        $airmax->images()->firstOrCreate(
            ['url' => 'https://placehold.co/800x800?text=Air+Max+90'],
            ['alt' => 'Air Max 90 side view', 'sort_order' => 1]
        );

        $ultra = Product::firstOrCreate(
            ['slug' => 'ultraboost'],
            [
                'name'        => 'UltraBoost',
                'brand_id'    => $adidas->id,
                'base_price'  => 180.00,
                'description' => 'Responsive Boost midsole and a Primeknit upper for a '
                    .'snug, sock-like fit on every run.',
                'size_guide'  => 'Runs slightly small — consider sizing up half a size.',
            ]
        );
        $this->variants($ultra, [
            [$red,  $s42, 'UB-RED-42', 6],
            [$blue, $s43, 'UB-BLU-43', 5],
        ]);

        $suede = Product::firstOrCreate(
            ['slug' => 'suede-classic'],
            [
                'name'        => 'Suede Classic',
                'brand_id'    => $puma->id,
                'base_price'  => 85.00,
                'description' => 'A street icon since 1968 — soft suede upper and the '
                    .'signature Puma Formstrip.',
                'size_guide'  => 'Fits true to size.',
            ]
        );
        $this->variants($suede, [
            [$red,  $s42, 'SC-RED-42', 13],
            [$blue, $s43, 'SC-BLU-43', 12],
        ]);

        $pegasus = Product::firstOrCreate(
            ['slug' => 'pegasus-41'],
            [
                'name'        => 'Pegasus 41',
                'brand_id'    => $nike->id,
                'base_price'  => 130.00,
                'description' => 'A springy, everyday trainer with Nike ReactX foam and '
                    .'dual Air Zoom units.',
                'size_guide'  => 'Fits true to size.',
            ]
        );
        $this->variants($pegasus, [
            [$red, $s42, 'PEG-RED-42', 11],
            [$red, $s43, 'PEG-RED-43', 6],
        ]);

        Review::firstOrCreate(
            ['product_id' => $airmax->id, 'user_id' => $alice->id],
            ['rating' => 4, 'comment' => 'Super comfortable and looks great.', 'is_verified' => true]
        );
        Review::firstOrCreate(
            ['product_id' => $airmax->id, 'user_id' => $bob->id],
            ['rating' => 4, 'comment' => 'Solid everyday shoe.', 'is_verified' => true]
        );
        Review::firstOrCreate(
            ['product_id' => $ultra->id, 'user_id' => $alice->id],
            ['rating' => 4, 'comment' => 'Great for running.', 'is_verified' => false]
        );
    }

    /**
     * @param  array<int, array{0: Color, 1: Size, 2: string, 3: int}>  $rows
     */
    private function variants(Product $product, array $rows): void
    {
        foreach ($rows as [$color, $size, $sku, $stock]) {
            $product->variants()->firstOrCreate(
                ['sku' => $sku],
                [
                    'color_id'       => $color->id,
                    'size_id'        => $size->id,
                    'price'          => null,
                    'stock_quantity' => $stock,
                ]
            );
        }
    }
}