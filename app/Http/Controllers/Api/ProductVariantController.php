<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreVariantRequest;
use App\Http\Requests\Product\UpdateVariantRequest;
use App\Http\Resources\ProductVariantResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;

/**
 * Manage the variants (colour/size/stock/price) of a shoe — the data
 * behind the variant picker on the detailed product page.
 *
 *   GET    /api/products/{product}/variants
 *   POST   /api/products/{product}/variants
 *   PUT    /api/variants/{variant}
 *   DELETE /api/variants/{variant}
 */
class ProductVariantController extends Controller
{
    /** List a product's variants. */
    public function index(Product $product): JsonResponse
    {
        $variants = $product->variants()->with(['color', 'size'])->get();

        return ProductVariantResource::collection($variants)->response();
    }

    /** Add a variant to a product. */
    public function store(StoreVariantRequest $request, Product $product): JsonResponse
    {
        $variant = $product->variants()->create($request->validated());

        return ProductVariantResource::make($variant->load(['color', 'size']))
            ->response()
            ->setStatusCode(201);
    }

    /** Update an existing variant (typically stock or price). */
    public function update(UpdateVariantRequest $request, ProductVariant $variant): ProductVariantResource
    {
        $variant->update($request->validated());

        return ProductVariantResource::make($variant->fresh()->load(['color', 'size']));
    }

    /** Remove a variant. */
    public function destroy(ProductVariant $variant): JsonResponse
    {
        $variant->delete();

        return response()->json(null, 204);
    }
}
