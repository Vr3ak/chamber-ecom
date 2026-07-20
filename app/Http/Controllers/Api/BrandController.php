<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreBrandRequest;
use App\Http\Requests\Catalog\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 *   GET    /api/brands        list (+ products_count, is_active)
 *   POST   /api/brands        create               (admin)
 *   PUT    /api/brands/{brand} update              (admin)
 *   DELETE /api/brands/{brand} delete               (admin)
 */
class BrandController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BrandResource::collection(
            Brand::query()->withCount('products')->orderBy('name')->get()
        );
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $brand = Brand::create($request->validated())->fresh();

        return BrandResource::make($brand->loadCount('products'))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): BrandResource
    {
        $brand->update($request->validated());

        return BrandResource::make($brand->fresh()->loadCount('products'));
    }

    public function destroy(Brand $brand): JsonResponse
    {
        $brand->delete();

        return response()->json(null, 204);
    }
}
