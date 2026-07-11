<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductListResource;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * Shoe (product) CRUD + the Feature 2 Filter/Search endpoint.
 *
 *   GET    /api/products            list + filter/search (Feature 2)
 *   GET    /api/filters             the available filter options (facets)
 *   POST   /api/products            create a shoe (+ optional variants)
 *   GET    /api/products/{product}  detailed product page (Feature 1)
 *   PUT    /api/products/{product}  update a shoe
 *   DELETE /api/products/{product}  delete a shoe
 */
class ProductController extends Controller
{
    /**
     * Feature 2 — Filter / Search.
     * Every filter is optional and they combine (AND) together:
     *   ?q=air                     keyword on the product name
     *   ?brand_id=1  or ?brand_id=1,2   one or many brands
     *   ?color_id=1,3              one or many colours
     *   ?size_id=3                 one or many sizes
     *   ?min_price=80&max_price=150 price range (uses the base price)
     *   ?sort=price_asc|price_desc|top_rated|newest
     *   ?per_page=12&page=2        pagination
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $brandIds = $this->ids($request->input('brand_id'));
        $colorIds = $this->ids($request->input('color_id'));
        $sizeIds  = $this->ids($request->input('size_id'));

        $products = Product::query()
            ->active()
            ->with(['brand', 'images'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            // keyword search on the name
            ->when($request->filled('q'), fn ($qb) =>
                $qb->where('name', 'like', '%'.$request->string('q').'%'))
            // brand filter (one or many)
            ->when($brandIds, fn ($qb) => $qb->whereIn('brand_id', $brandIds))
            // price range on base_price
            ->when($request->filled('min_price'), fn ($qb) =>
                $qb->where('base_price', '>=', $request->float('min_price')))
            ->when($request->filled('max_price'), fn ($qb) =>
                $qb->where('base_price', '<=', $request->float('max_price')))
            // colour / size live on the variants
            ->when($colorIds, fn ($qb) =>
                $qb->whereHas('variants', fn ($v) => $v->whereIn('color_id', $colorIds)))
            ->when($sizeIds, fn ($qb) =>
                $qb->whereHas('variants', fn ($v) => $v->whereIn('size_id', $sizeIds)))
            // sorting
            ->when($request->input('sort') === 'price_asc',  fn ($qb) => $qb->orderBy('base_price'))
            ->when($request->input('sort') === 'price_desc', fn ($qb) => $qb->orderByDesc('base_price'))
            ->when($request->input('sort') === 'top_rated',  fn ($qb) => $qb->orderByDesc('reviews_avg_rating'))
            ->when(in_array($request->input('sort'), [null, 'newest'], true), fn ($qb) => $qb->latest())
            ->paginate($request->integer('per_page', 12))
            ->withQueryString();

        return ProductListResource::collection($products);
    }

    /**
     * Feature 2 — the filter panel options (facets) the sidebar renders:
     * brands (with how many active products each has), colours, sizes,
     * and the min/max price across the catalogue.
     */
    public function filters(): JsonResponse
    {
        return response()->json([
            'brands' => Brand::query()
                ->whereHas('products', fn ($p) => $p->where('is_active', true))
                ->withCount(['products' => fn ($p) => $p->where('is_active', true)])
                ->orderBy('name')
                ->get(['id', 'name', 'slug'])
                ->map(fn ($b) => [
                    'id' => $b->id, 'name' => $b->name, 'slug' => $b->slug,
                    'products_count' => $b->products_count,
                ]),
            'colors' => Color::query()
                ->whereHas('variants.product', fn ($p) => $p->where('is_active', true))
                ->orderBy('name')
                ->get(['id', 'name', 'hex_code']),
            'sizes' => Size::query()
                ->whereHas('variants.product', fn ($p) => $p->where('is_active', true))
                ->orderBy('sort_order')
                ->get(['id', 'label', 'foot_length_cm']),
            'price' => [
                'min' => (float) Product::where('is_active', true)->min('base_price'),
                'max' => (float) Product::where('is_active', true)->max('base_price'),
            ],
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        $product = DB::transaction(function () use ($data) {
            $product = Product::create($data);

            foreach ($data['variants'] ?? [] as $variant) {
                $product->variants()->create($variant);
            }

            if (! empty($data['category_ids'])) {
                $product->categories()->sync($data['category_ids']);
            }

            return $product;
        });

        return ProductDetailResource::make($this->loadDetail($product))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * The Detailed Product Page payload (Feature 1) + related shoes.
     */
    public function show(Product $product): JsonResponse
    {
        $product = $this->loadDetail($product);

        $related = Product::query()
            ->active()
            ->where('brand_id', $product->brand_id)
            ->whereKeyNot($product->id)
            ->with(['brand', 'images'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->limit(4)
            ->get();

        return ProductDetailResource::make($product)
            ->additional(['related' => ProductListResource::collection($related)])
            ->response();
    }

    public function update(UpdateProductRequest $request, Product $product): ProductDetailResource
    {
        $data = $request->validated();
        $product->update($data);

        if (array_key_exists('category_ids', $data)) {
            $product->categories()->sync($data['category_ids'] ?? []);
        }

        return ProductDetailResource::make($this->loadDetail($product->fresh()));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }

    /**
     * Turn a filter value into a clean array of ids. Accepts a single id,
     * a comma list ("1,3"), or an array (brand_id[]=1&brand_id[]=2).
     *
     * @return array<int, int>
     */
    private function ids(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $value = is_array($value) ? $value : explode(',', (string) $value);

        return collect($value)
            ->map(fn ($v) => (int) trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function loadDetail(Product $product): Product
    {
        return $product->load([
                'brand',
                'images',
                'variants.color',
                'variants.size',
                'reviews.user',
                'categories.parent',
                'trending',
            ])
            ->loadCount('reviews')
            ->loadAvg('reviews', 'rating')
            ->loadSum('variants', 'stock_quantity');
    }
}
