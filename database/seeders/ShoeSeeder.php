<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Models\Trending;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the Chamber storefront catalogue matching the Figma:
 * Urban Threads Co. / Trailhead Gear / Comfort Walk / Classic Co. brands,
 * US sizes (7–12), and shoes grouped under the Men / Women / Kids
 * top-level categories that the /men /women /kids pages browse.
 *
 * Additive and idempotent (firstOrCreate) — runs alongside ChamberSeeder.
 */
class ShoeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first();
        $reviewers = User::whereIn('email', ['dara@gmail.com', 'lina@gmail.com', 'sopheak@gmail.com'])->get();

        // ---- brands ----
        $brands = collect([
            'Urban Threads Co.' => 'urban-threads',
            'Trailhead Gear' => 'trailhead-gear',
            'Comfort Walk' => 'comfort-walk',
            'Classic Co.' => 'classic-co',
        ])->mapWithKeys(fn ($slug, $name) => [
            $slug => Brand::firstOrCreate(['name' => $name], ['slug' => $slug]),
        ]);

        // ---- categories (top-level, browsed by /men /women /kids) ----
        $cats = collect(['Men' => 'men', 'Women' => 'women', 'Kids' => 'kids'])
            ->mapWithKeys(fn ($slug, $name) => [
                $slug => Category::firstOrCreate(['slug' => $slug], ['name' => $name]),
            ]);

        // ---- colours ----
        $colors = collect([
            'Black' => '#222222', 'White' => '#F2F2F2', 'Gray' => '#9CA3AF', 'Brown' => '#8B5E3C',
        ])->mapWithKeys(fn ($hex, $name) => [
            $name => Color::firstOrCreate(['name' => $name], ['hex_code' => $hex]),
        ]);

        // ---- US sizes ----
        $sizeData = [['7', 24.0], ['8', 25.0], ['9', 26.0], ['9.5', 26.5], ['10', 27.0], ['10.5', 27.5], ['11', 28.0], ['12', 29.0]];
        $sizes = collect($sizeData)->mapWithKeys(fn ($row, $i) => [
            $row[0] => Size::firstOrCreate(['label' => $row[0]], ['foot_length_cm' => $row[1], 'sort_order' => 10 + $i]),
        ]);

        // name, brand slug, price, gender categories, colours, size labels, trending?, rating
        $catalogue = [
            ['Air Glide Runner',   'urban-threads',  59, ['men'],          ['Black', 'White', 'Gray', 'Brown'], ['7', '8', '9', '9.5', '10', '10.5', '11', '12'], true,  4.2],
            ['Trail Blazer X',     'trailhead-gear', 79, ['men'],          ['Black', 'Brown'],                  ['8', '9', '10', '11', '12'],                     true,  4.6],
            ['Classic Court',      'classic-co',     49, ['men', 'women'], ['White', 'Black'],                  ['7', '8', '9', '10', '11'],                      true,  4.4],
            ['Urban Street Low',   'urban-threads',  49, ['men'],          ['Black', 'White'],                  ['8', '9', '10', '11', '12'],                     true,  4.1],
            ['Marathon Pro',       'trailhead-gear', 89, ['men'],          ['Gray', 'Black'],                   ['8', '9', '10', '11', '12'],                     false, 4.7],
            ['Cross Trainer II',   'comfort-walk',   69, ['men', 'women'], ['White', 'Gray'],                   ['7', '8', '9', '10', '11'],                      false, 4.3],
            ['Street Flex',        'urban-threads',  54, ['men'],          ['Black', 'Brown'],                  ['8', '9', '10', '11', '12'],                     true,  4.0],
            ['All-Terrain Hiker',  'trailhead-gear', 95, ['men'],          ['Brown', 'Black'],                  ['9', '10', '11', '12'],                          false, 4.8],
            ['Featherlight Sport', 'comfort-walk',   69, ['women', 'men'], ['Gray', 'White'],                   ['7', '8', '9', '10', '11'],                      true,  4.5],
            ['Studio Flex',        'urban-threads',  59, ['women'],        ['Black', 'White'],                  ['7', '8', '9', '9.5', '10'],                     true,  4.2],
            ['Comfort Walk Plus',  'comfort-walk',   59, ['women'],        ['Brown', 'Gray'],                   ['7', '8', '9', '10', '11'],                      true,  4.4],
            ['All-Day Slip-On',    'classic-co',     59, ['women', 'kids'], ['Black', 'Brown'],                  ['7', '8', '9', '10'],                            true,  4.1],
            ['Little Sprinter',    'comfort-walk',   39, ['kids'],         ['White', 'Gray'],                   ['7', '8', '9'],                                  false, 4.6],
            ['Playground Pro',     'urban-threads',  42, ['kids'],         ['Black', 'White'],                  ['7', '8', '9'],                                  true,  4.3],
            ['Mini Trail',         'trailhead-gear', 45, ['kids'],         ['Brown', 'Gray'],                   ['7', '8', '9'],                                  false, 4.5],
        ];

        $sortOrder = 10;
        foreach ($catalogue as [$name, $brandSlug, $price, $genders, $colorNames, $sizeLabels, $trending, $rating]) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'brand_id' => $brands[$brandSlug]->id,
                    'name' => $name,
                    'base_price' => $price,
                    'description' => "The {$name} combines lightweight cushioning with responsive support for all-day comfort. "
                        .'Featuring a breathable upper, reinforced heel counter, and our signature FlexSole technology, '
                        .'this shoe adapts to your stride for a smooth, natural feel.',
                ]
            );

            $product->categories()->syncWithoutDetaching(
                collect($genders)->map(fn ($g) => $cats[$g]->id)->all()
            );

            // one primary image row per colour (placeholder path)
            $product->images()->firstOrCreate(
                ['url' => '/img/'.$product->slug.'.jpg'],
                ['alt' => $name, 'sort_order' => 0, 'is_primary' => true]
            );

            // variants: every colour × size
            foreach ($colorNames as $cn) {
                foreach ($sizeLabels as $sl) {
                    $product->variants()->firstOrCreate(
                        ['color_id' => $colors[$cn]->id, 'size_id' => $sizes[$sl]->id],
                        ['price' => null, 'stock_quantity' => random_int(3, 20)]
                    );
                }
            }

            // a couple of reviews so the rating badge has data
            if ($reviewers->isNotEmpty()) {
                $product->reviews()->firstOrCreate(
                    ['user_id' => $reviewers->first()->id],
                    ['rating' => (int) round($rating), 'body' => 'Great fit and super comfortable.', 'is_verified' => true]
                );
                if ($reviewers->count() > 1) {
                    $product->reviews()->firstOrCreate(
                        ['user_id' => $reviewers->get(1)->id],
                        ['rating' => max(1, (int) round($rating) - 1), 'body' => 'Looks great, runs true to size.', 'is_verified' => false]
                    );
                }
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
