<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Review;
use App\Models\Size;
use App\Models\Trending;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the Chamber catalogue with the sample data from chamber_2.sql so
 * the documented query results hold (variant picker, breadcrumb, trending,
 * ratings). Everything is idempotent (firstOrCreate).
 */
class ChamberSeeder extends Seeder
{
    public function run(): void
    {
        // ---- admin (owner of trending entries) ----
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@chamber.test'],
            ['name' => 'Chamber Admin', 'password' => Hash::make('password'), 'role' => 'superadmin']
        );

        // ---- users (review authors) ----
        $dara    = User::firstOrCreate(['email' => 'dara@gmail.com'],    ['name' => 'Dara Sok',     'password' => Hash::make('password')]);
        $lina    = User::firstOrCreate(['email' => 'lina@gmail.com'],    ['name' => 'Lina Chan',    'password' => Hash::make('password')]);
        $sopheak = User::firstOrCreate(['email' => 'sopheak@gmail.com'], ['name' => 'Sopheak Meas', 'password' => Hash::make('password')]);

        // ---- brands ----
        $nike   = Brand::firstOrCreate(['name' => 'Nike'],   ['slug' => 'nike',   'logo_image' => '/img/nike.png']);
        $adidas = Brand::firstOrCreate(['name' => 'Adidas'], ['slug' => 'adidas', 'logo_image' => '/img/adidas.png']);
        $puma   = Brand::firstOrCreate(['name' => 'Puma'],   ['slug' => 'puma',   'logo_image' => '/img/puma.png']);

        // ---- categories (Footwear > Sneakers > Running, + Boots) ----
        $footwear = Category::firstOrCreate(['slug' => 'footwear'], ['name' => 'Footwear']);
        $sneakers = Category::firstOrCreate(['slug' => 'sneakers'], ['name' => 'Sneakers', 'parent_id' => $footwear->id]);
        $running  = Category::firstOrCreate(['slug' => 'running'],  ['name' => 'Running',  'parent_id' => $sneakers->id]);
        Category::firstOrCreate(['slug' => 'boots'], ['name' => 'Boots', 'parent_id' => $footwear->id]);

        // ---- colors ----
        $red   = Color::firstOrCreate(['name' => 'Red'],   ['hex_code' => '#E24B4A']);
        $blue  = Color::firstOrCreate(['name' => 'Blue'],  ['hex_code' => '#378ADD']);
        $black = Color::firstOrCreate(['name' => 'Black'], ['hex_code' => '#222222']);
        Color::firstOrCreate(['name' => 'White'], ['hex_code' => '#F2F2F2']);

        // ---- sizes ----
        Size::firstOrCreate(['label' => '40'], ['foot_length_cm' => 25.0, 'sort_order' => 1]);
        $s41 = Size::firstOrCreate(['label' => '41'], ['foot_length_cm' => 25.8, 'sort_order' => 2]);
        $s42 = Size::firstOrCreate(['label' => '42'], ['foot_length_cm' => 26.5, 'sort_order' => 3]);
        $s43 = Size::firstOrCreate(['label' => '43'], ['foot_length_cm' => 27.3, 'sort_order' => 4]);

        // ---- product 1: Air Max 90 (Nike, 120) ----
        $airmax = Product::firstOrCreate(['slug' => 'air-max-90'], [
            'brand_id' => $nike->id, 'name' => 'Air Max 90', 'base_price' => 120.00,
            'description' => 'Classic cushioned sneaker with the iconic Waffle outsole and visible Air.',
        ]);
        $airmax->categories()->syncWithoutDetaching([$sneakers->id, $running->id]);
        $this->variants($airmax, [
            [$red,  $s42, null, 12],
            [$red,  $s43, null, 8],
            [$blue, $s42, null, 5],
        ]);
        $airmax->images()->firstOrCreate(['url' => '/img/am90-red.jpg'],  ['color_id' => $red->id,  'alt' => 'Air Max 90 red',  'sort_order' => 0, 'is_primary' => true]);
        $airmax->images()->firstOrCreate(['url' => '/img/am90-blue.jpg'], ['color_id' => $blue->id, 'alt' => 'Air Max 90 blue', 'sort_order' => 1, 'is_primary' => false]);

        // ---- product 2: UltraBoost (Adidas, 150) ----
        $ultra = Product::firstOrCreate(['slug' => 'ultraboost'], [
            'brand_id' => $adidas->id, 'name' => 'UltraBoost', 'base_price' => 150.00,
            'description' => 'Responsive Boost midsole and a Primeknit upper for every run.',
        ]);
        $ultra->categories()->syncWithoutDetaching([$running->id]);
        $this->variants($ultra, [
            [$black, $s42, 160.00, 7],
            [$black, $s43, 160.00, 4],
        ]);
        $ultra->images()->firstOrCreate(['url' => '/img/ub-black.jpg'], ['color_id' => $black->id, 'alt' => 'UltraBoost black', 'sort_order' => 0, 'is_primary' => true]);

        // ---- product 3: Suede Classic (Puma, 80) ----
        $suede = Product::firstOrCreate(['slug' => 'suede-classic'], [
            'brand_id' => $puma->id, 'name' => 'Suede Classic', 'base_price' => 80.00,
            'description' => 'A street icon since 1968 — soft suede upper and the signature Formstrip.',
        ]);
        $suede->categories()->syncWithoutDetaching([$sneakers->id]);
        $this->variants($suede, [
            [$black, $s41, null, 15],
        ]);
        $suede->images()->firstOrCreate(['url' => '/img/suede-black.jpg'], ['color_id' => $black->id, 'alt' => 'Suede black', 'sort_order' => 0, 'is_primary' => true]);

        // ---- trending (admin curates featured shoes) ----
        Trending::firstOrCreate(['product_id' => $airmax->id], ['admin_id' => $admin->id, 'sort_order' => 1, 'is_active' => true]);
        Trending::firstOrCreate(['product_id' => $ultra->id],  ['admin_id' => $admin->id, 'sort_order' => 2, 'is_active' => true]);

        // ---- reviews (Air Max 90 -> avg 4.5 over 2) ----
        Review::firstOrCreate(['product_id' => $airmax->id, 'user_id' => $dara->id],
            ['rating' => 5, 'body' => 'Super comfy, true to size!', 'is_verified' => true]);
        Review::firstOrCreate(['product_id' => $airmax->id, 'user_id' => $sopheak->id],
            ['rating' => 4, 'body' => 'Looks great in photos.', 'is_verified' => false]);
    }

    /**
     * Create variants for a product. Each row: [Color, Size, price|null, stock].
     *
     * @param  array<int, array{0: Color, 1: Size, 2: float|null, 3: int}>  $rows
     */
    private function variants(Product $product, array $rows): void
    {
        foreach ($rows as [$color, $size, $price, $stock]) {
            $product->variants()->firstOrCreate(
                ['color_id' => $color->id, 'size_id' => $size->id],
                ['price' => $price, 'stock_quantity' => $stock]
            );
        }
    }
}
