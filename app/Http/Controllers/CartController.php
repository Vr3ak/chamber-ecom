<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The logged-in customer's shopping cart. Session-guarded (never accepts a
 * user_id from the request) — every action works against auth()->user()'s
 * own active cart only.
 *
 *   GET    /cart
 *   POST   /cart/items
 *   PATCH  /cart/items/{cartItem}
 *   DELETE /cart/items/{cartItem}
 *   DELETE /cart
 */
class CartController extends Controller
{
    public function show(Request $request): JsonResponse|Response
    {
        $cart = $this->loadDetail(Cart::activeFor($request->user()));

        if (! $this->wantsJson($request)) {
            return Inertia::render('shop/cart', [
                'cart' => CartResource::make($cart)->resolve(),
            ]);
        }

        // Force 200: JsonResource defaults to 201 when the underlying model
        // was just lazily created by activeFor(), which is an implementation
        // detail — a GET should never report "created" to the caller.
        return CartResource::make($cart)->response()->setStatusCode(200);
    }

    public function addItem(AddCartItemRequest $request): CartResource|RedirectResponse
    {
        $data = $request->validated();
        $cart = Cart::activeFor($request->user());
        $variant = isset($data['product_variant_id'])
            ? ProductVariant::findOrFail($data['product_variant_id'])
            : $this->resolveDefaultVariant($data['product_id']);

        $item = $cart->items()->where('product_variant_id', $variant->id)->first();
        $quantity = $data['quantity'] + ($item?->quantity ?? 0);

        $this->assertInStock($variant, $quantity);

        if ($item) {
            $item->update(['quantity' => $quantity]);
        } else {
            $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => $quantity]);
        }

        return $this->respond($request, $cart->fresh());
    }

    public function updateItem(UpdateCartItemRequest $request, CartItem $cartItem): CartResource|RedirectResponse
    {
        $this->authorizeOwner($request, $cartItem);

        $this->assertInStock($cartItem->variant, $request->validated('quantity'));
        $cartItem->update(['quantity' => $request->validated('quantity')]);

        return $this->respond($request, $cartItem->cart);
    }

    public function removeItem(Request $request, CartItem $cartItem): CartResource|RedirectResponse
    {
        $this->authorizeOwner($request, $cartItem);

        $cart = $cartItem->cart;
        $cartItem->delete();

        return $this->respond($request, $cart);
    }

    public function clear(Request $request): CartResource|RedirectResponse
    {
        $cart = Cart::activeFor($request->user());
        $cart->items()->delete();

        return $this->respond($request, $cart);
    }

    /**
     * API callers get the cart resource back; the Inertia page just needs the
     * redirect so its `cart` prop is re-resolved from show().
     */
    private function respond(Request $request, Cart $cart): CartResource|RedirectResponse
    {
        if (! $this->wantsJson($request)) {
            return back();
        }

        return CartResource::make($this->loadDetail($cart));
    }

    private function resolveDefaultVariant(int $productId): ProductVariant
    {
        $variant = Product::findOrFail($productId)->defaultVariant();

        abort_if(! $variant, 422, 'This product has no purchasable variants.');

        return $variant;
    }

    private function authorizeOwner(Request $request, CartItem $cartItem): void
    {
        abort_if($cartItem->cart->user_id !== $request->user()->id, 404);
    }

    private function assertInStock(ProductVariant $variant, int $quantity): void
    {
        if ($variant->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => ["Only {$variant->stock_quantity} left in stock."],
            ]);
        }
    }

    private function loadDetail(Cart $cart): Cart
    {
        return $cart->load(['items.variant.color', 'items.variant.size', 'items.variant.product.images']);
    }
}
