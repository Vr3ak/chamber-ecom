<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductListResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->active()
            ->with(['brand', 'images'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->when($request->filled('brand_id'), fn ($q) =>
                $q->where('brand_id', $request->integer('brand_id')))
            ->when($request->filled('q'), fn ($q) =>
                $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('min_price'), fn ($q) =>
                $q->where('base_price', '>=', $request->float('min_price')))
            ->when($request->filled('max_price'), fn ($q) =>
                $q->where('base_price', '<=', $request->float('max_price')))
            ->when($request->filled('color_id'), fn ($q) =>
                $q->whereHas('variants', fn ($v) => $v->where('color_id', $request->integer('color_id'))))
            ->when($request->filled('size_id'), fn ($q) =>
                $q->whereHas('variants', fn ($v) => $v->where('size_id', $request->integer('size_id'))))
            ->when($request->input('sort') === 'price_asc', fn ($q) => $q->orderBy('base_price'))
            ->when($request->input('sort') === 'price_desc', fn ($q) => $q->orderByDesc('base_price'))
            ->when($request->input('sort') === 'top_rated', fn ($q) => $q->orderByDesc('reviews_avg_rating'))
            ->when(! $request->filled('sort'), fn ($q) => $q->latest())
            ->paginate($request->integer('per_page', 12))
            ->withQueryString();

        return ProductListResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        $product = DB::transaction(function () use ($data) {
            $product = Product::create($data);

            foreach ($data['variants'] ?? [] as $variant) {
                $product->variants()->create($variant);
            }

            return $product;
        });

        return ProductDetailResource::make($this->loadDetail($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductDetailResource
    {
        return ProductDetailResource::make($this->loadDetail($product));
    }

    public function update(UpdateProductRequest $request, Product $product): ProductDetailResource
    {
        $product->update($request->validated());

        return ProductDetailResource::make($this->loadDetail($product->fresh()));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }

    private function loadDetail(Product $product): Product
    {
        return $product->load([
                'brand',
                'images',
                'variants.color',
                'variants.size',
                'reviews.user',
            ])
            ->loadCount('reviews')
            ->loadAvg('reviews', 'rating')
            ->loadSum('variants', 'stock_quantity');
    }
}