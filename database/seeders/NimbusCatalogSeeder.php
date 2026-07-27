<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Models\Trending;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Extra catalogue: Nimbus / Volt / Terra / Aero brands (from the test data
 * sheet). Additive and idempotent (firstOrCreate) — reuses existing colours,
 * sizes, and categories by name instead of duplicating them.
 *
 * Note: the sheet's "Air Glide Runner" collides with an existing product
 * of that slug (a different, unrelated Urban Threads Co. shoe), so this
 * one is seeded as "Nimbus Air Glide Runner" to keep its own slug.
 */
class NimbusCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first();

        // ---- brands (all new) ----
        $brands = collect([
            'Nimbus' => 'nimbus', 'Volt' => 'volt', 'Terra' => 'terra', 'Aero' => 'aero',
        ])->mapWithKeys(fn ($slug, $name) => [
            $name => Brand::firstOrCreate(['name' => $name], ['slug' => $slug]),
        ]);

        // ---- categories (reuse Men/Women/Running, add Basketball/Lifestyle) ----
        $men = Category::firstOrCreate(['slug' => 'men'], ['name' => 'Men']);
        $women = Category::firstOrCreate(['slug' => 'women'], ['name' => 'Women']);
        $running = Category::firstOrCreate(['slug' => 'running'], ['name' => 'Running']);
        $basketball = Category::firstOrCreate(['slug' => 'basketball'], ['name' => 'Basketball', 'parent_id' => $men->id]);
        $lifestyle = Category::firstOrCreate(['slug' => 'lifestyle'], ['name' => 'Lifestyle', 'parent_id' => $women->id]);

        // ---- colours (reuse existing Black/White/Red/Blue/Gray, add Green) ----
        $colors = collect([
            'Black' => '#111111', 'White' => '#F5F5F5', 'Red' => '#E24B4A',
            'Blue' => '#2E5B94', 'Green' => '#3E8E41', 'Gray' => '#8A97A6',
        ])->mapWithKeys(fn ($hex, $name) => [
            $name => Color::firstOrCreate(['name' => $name], ['hex_code' => $hex]),
        ]);

        // ---- sizes (reuse existing 40-43, add 39/44) ----
        $sizeData = [['39', 24.5], ['40', 25.0], ['41', 25.5], ['42', 26.0], ['43', 26.7], ['44', 27.3]];
        $sizes = collect($sizeData)->mapWithKeys(fn ($row) => [
            $row[0] => Size::firstOrCreate(['label' => $row[0]], ['foot_length_cm' => $row[1], 'sort_order' => 20 + (int) $row[0]]),
        ]);

        // name, brand, price, categories, colour+size variants [color, size, price|null, stock], trending?
        $catalogue = [
            ['Nimbus Air Glide Runner', 'Nimbus', 120, [$running], [
                ['Black', '42', null, 12], ['Black', '41', null, 5], ['Red', '42', null, 0],
            ], true],
            ['Volt Street Low', 'Volt', 95, [$lifestyle], [
                ['White', '40', null, 20], ['Blue', '41', null, 8],
            ], false],
            ['Terra Trail X', 'Terra', 140, [$running], [
                ['Green', '43', null, 15], ['Gray', '44', null, 3],
            ], true],
            ['Aero Comfort Walk', 'Aero', 80, [$men], [
                ['Black', '39', null, 25], ['White', '42', null, 10],
            ], false],
            ['Nimbus Studio Flex', 'Nimbus', 110, [$women, $running], [
                ['Blue', '42', null, 18], ['Red', '43', null, 6],
            ], false],
            ['Volt Court Classic', 'Volt', 160, [$men, $basketball], [
                ['Black', '44', 175.00, 9], ['Gray', '41', null, 0],
            ], true],
        ];

        $sortOrder = 30;
        foreach ($catalogue as [$name, $brandName, $price, $categories, $variants, $trending]) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'brand_id' => $brands[$brandName]->id,
                    'name' => $name,
                    'base_price' => $price,
                    'description' => "The {$name} — built for everyday comfort and performance.",
                ]
            );

            $product->categories()->syncWithoutDetaching(
                collect($categories)->map(fn (Category $c) => $c->id)->all()
            );

            $product->images()->firstOrCreate(
                ['url' => '/img/'.$product->slug.'.jpg'],
                ['alt' => $name, 'sort_order' => 0, 'is_primary' => true]
            );

            foreach ($variants as [$colorName, $sizeLabel, $variantPrice, $stock]) {
                $product->variants()->firstOrCreate(
                    ['color_id' => $colors[$colorName]->id, 'size_id' => $sizes[$sizeLabel]->id],
                    ['price' => $variantPrice, 'stock_quantity' => $stock]
                );
            }

            if ($trending && $admin) {
                Trending::firstOrCreate(
                    ['product_id' => $product->id],
                    ['admin_id' => $admin->id, 'sort_order' => $sortOrder, 'is_active' => true]
                );
            }
            $sortOrder++;
        }
    }
}
