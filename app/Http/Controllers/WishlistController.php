<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wishlist\AddWishlistItemRequest;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function show(Request $request): JsonResponse|Response
    {
        $wishlist = $this->loadDetail(Wishlist::defaultFor($request->user()));

        if (! $this->wantsJson($request)) {
            return Inertia::render('shop/wishlist', [
                'wishlist' => WishlistResource::make($wishlist)->resolve(),
            ]);
        }

        return WishlistResource::make($wishlist)->response()->setStatusCode(200);
    }

    public function addItem(AddWishlistItemRequest $request): WishlistResource|RedirectResponse
    {
        $wishlist = Wishlist::defaultFor($request->user());
        $productId = $request->validated('product_id');

        if (! $wishlist->items()->where('product_id', $productId)->exists()) {
            $wishlist->items()->create(['product_id' => $productId, 'created_at' => now()]);
        }

        return $this->respond($request, $wishlist->fresh());
    }

    public function removeItem(Request $request, WishlistItem $wishlistItem): WishlistResource|RedirectResponse
    {
        abort_if($wishlistItem->wishlist->user_id !== $request->user()->id, 404);

        $wishlist = $wishlistItem->wishlist;
        $wishlistItem->delete();

        return $this->respond($request, $wishlist);
    }

    private function respond(Request $request, Wishlist $wishlist): WishlistResource|RedirectResponse
    {
        if (! $this->wantsJson($request)) {
            return back();
        }

        return WishlistResource::make($this->loadDetail($wishlist));
    }

    private function loadDetail(Wishlist $wishlist): Wishlist
    {
        return $wishlist->load('items.product.images');
    }
}
