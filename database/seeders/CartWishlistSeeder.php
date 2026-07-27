<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

/**
 * Seeds a cart and a wishlist with real items so /cart and /wishlist have
 * something to show without manually adding items through the UI/API first.
 */
class CartWishlistSeeder extends Seeder
{
    public function run(): void
    {
        $dara = User::where('email', 'dara@gmail.com')->first();
        $lina = User::where('email', 'lina@gmail.com')->first();

        if (! $dara || ! $lina) {
            return;
        }

        $ultra = Product::where('slug', 'ultraboost')->first();
        $suede = Product::where('slug', 'suede-classic')->first();
        $airmax = Product::where('slug', 'air-max-90')->first();

        // ---- Dara's cart: one UltraBoost (Black/42), two Suede Classic (Black/41) ----
        $cart = Cart::activeFor($dara);

        if ($ultra) {
            $variant = $ultra->variants()->whereHas('color', fn ($q) => $q->where('name', 'Black'))
                ->whereHas('size', fn ($q) => $q->where('label', '42'))->first();

            if ($variant) {
                $cart->items()->firstOrCreate(['product_variant_id' => $variant->id], ['quantity' => 1]);
            }
        }

        if ($suede) {
            $variant = $suede->variants()->whereHas('color', fn ($q) => $q->where('name', 'Black'))
                ->whereHas('size', fn ($q) => $q->where('label', '41'))->first();

            if ($variant) {
                $cart->items()->firstOrCreate(['product_variant_id' => $variant->id], ['quantity' => 2]);
            }
        }

        // ---- Dara's wishlist: Air Max 90 ----
        $daraWishlist = Wishlist::defaultFor($dara);

        if ($airmax) {
            $daraWishlist->items()->firstOrCreate(['product_id' => $airmax->id], ['created_at' => now()]);
        }

        // ---- Lina's wishlist: UltraBoost + Suede Classic ----
        $linaWishlist = Wishlist::defaultFor($lina);

        foreach ([$ultra, $suede] as $product) {
            if ($product) {
                $linaWishlist->items()->firstOrCreate(['product_id' => $product->id], ['created_at' => now()]);
            }
        }
    }
}
