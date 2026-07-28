<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;

class ProductImageController extends Controller
{
    public function store(StoreProductImageRequest $request, Product $product): JsonResponse
    {
        $image = ProductImage::storeUpload($request->file('image'), $product->id, $request->validated());

        return response()->json(['data' => [
            'id' => $image->id,
            'url' => $image->url,
            'alt' => $image->alt,
            'color_id' => $image->color_id,
            'sort_order' => $image->sort_order,
            'is_primary' => $image->is_primary,
        ]], 201);
    }

    public function destroy(ProductImage $productImage): JsonResponse
    {
        $productImage->delete();

        return response()->json(null, 204);
    }
}
