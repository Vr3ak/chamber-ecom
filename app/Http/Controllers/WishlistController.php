<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wishlist\AddWishlistItemRequest;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The logged-in customer's default wishlist. Session-guarded, same
 * ownership rules as CartController.
 *
 *   GET    /wishlist
 *   POST   /wishlist/items
 *   DELETE /wishlist/items/{wishlistItem}
 */
class WishlistController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $wishlist = Wishlist::defaultFor($request->user());

        // Force 200: see the identical comment in CartController::show.
        return WishlistResource::make($this->loadDetail($wishlist))->response()->setStatusCode(200);
    }

    public function addItem(AddWishlistItemRequest $request): WishlistResource
    {
        $wishlist = Wishlist::defaultFor($request->user());
        $productId = $request->validated('product_id');

        // Idempotent: adding an already-saved product just returns it as-is.
        if (! $wishlist->items()->where('product_id', $productId)->exists()) {
            $wishlist->items()->create(['product_id' => $productId, 'created_at' => now()]);
        }

        return WishlistResource::make($this->loadDetail($wishlist->fresh()));
    }

    public function removeItem(Request $request, WishlistItem $wishlistItem): WishlistResource
    {
        abort_if($wishlistItem->wishlist->user_id !== $request->user()->id, 404);

        $wishlist = $wishlistItem->wishlist;
        $wishlistItem->delete();

        return WishlistResource::make($this->loadDetail($wishlist));
    }

    private function loadDetail(Wishlist $wishlist): Wishlist
    {
        return $wishlist->load('items.product.images');
    }
}
