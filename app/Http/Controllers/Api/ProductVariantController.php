<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreVariantRequest;
use App\Http\Requests\Product\UpdateVariantRequest;
use App\Http\Resources\ProductVariantResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;

class ProductVariantController extends Controller
{
    public function index(Product $product): JsonResponse
    {
        $variants = $product->variants()->with(['color', 'size'])->get();

        return ProductVariantResource::collection($variants)->response();
    }

    public function store(StoreVariantRequest $request, Product $product): JsonResponse
    {
        $variant = $product->variants()->create($request->validatedData());

        return ProductVariantResource::make($variant->load(['color', 'size']))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateVariantRequest $request, ProductVariant $variant): ProductVariantResource
    {
        $variant->update($request->validated());

        return ProductVariantResource::make($variant->fresh()->load(['color', 'size']));
    }

    public function destroy(ProductVariant $variant): JsonResponse
    {
        $variant->delete();

        return response()->json(null, 204);
    }
}