<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductListResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\Request;

/**
 * Storefront (Inertia) pages built on the Feature 1/2 catalogue:
 *   /{men|women|kids}   category listing with filters + pagination
 *   /products/{slug}    detailed product page
 */
class ShopController extends Controller
{
    private const TITLES = ['men' => "Men's Shoes", 'women' => "Women's Shoes", 'kids' => "Kids' Shoes"];

    public function category(Request $request, string $gender)
    {
        $category = Category::where('slug', $gender)->firstOrFail();

        $brandIds = $this->ids($request->input('brand_id'));
        $colorIds = $this->ids($request->input('color_id'));
        $sizeIds = $this->ids($request->input('size_id'));

        $inCategory = fn ($q) => $q->whereHas('categories', fn ($c) => $c->whereKey($category->id));

        $products = Product::query()
            ->active()
            ->tap($inCategory)
            ->with(['brand', 'images'])
            ->withCount(['reviews' => fn ($r) => $r->visible()])
            ->withAvg(['reviews' => fn ($r) => $r->visible()], 'rating')
            ->when($brandIds, fn ($q) => $q->whereIn('brand_id', $brandIds))
            ->when($request->filled('min_price'), fn ($q) => $q->where('base_price', '>=', $request->float('min_price')))
            ->when($request->filled('max_price'), fn ($q) => $q->where('base_price', '<=', $request->float('max_price')))
            ->when($colorIds, fn ($q) => $q->whereHas('variants', fn ($v) => $v->whereIn('color_id', $colorIds)))
            ->when($sizeIds, fn ($q) => $q->whereHas('variants', fn ($v) => $v->whereIn('size_id', $sizeIds)))
            ->when($request->input('sort') === 'price_asc', fn ($q) => $q->orderBy('base_price'))
            ->when($request->input('sort') === 'price_desc', fn ($q) => $q->orderByDesc('base_price'))
            ->when($request->input('sort') === 'top_rated', fn ($q) => $q->orderByDesc('reviews_avg_rating'))
            ->when(in_array($request->input('sort'), [null, 'featured', 'newest'], true), fn ($q) => $q->latest())
            ->paginate(9)
            ->withQueryString();

        return inertia('shop/category', [
            'gender' => $gender,
            'title' => self::TITLES[$gender],
            'products' => ProductListResource::collection($products)->resolve(),
            'pagination' => [
                'current' => $products->currentPage(),
                'last' => $products->lastPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
            'facets' => $this->facets($category),
            'active' => [
                'brand_id' => $brandIds,
                'color_id' => $colorIds,
                'size_id' => $sizeIds,
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'sort' => $request->input('sort', 'featured'),
            ],
        ]);
    }

    public function product(Product $product)
    {
        $product->load([
            'brand', 'images', 'variants.color', 'variants.size',
            'categories.parent', 'trending',
            'reviews' => fn ($q) => $q->visible()->with('user'),
        ])
            ->loadCount(['reviews' => fn ($q) => $q->visible()])
            ->loadAvg(['reviews' => fn ($q) => $q->visible()], 'rating')
            ->loadSum('variants', 'stock_quantity');

        $related = Product::query()
            ->active()
            ->where('brand_id', $product->brand_id)
            ->whereKeyNot($product->id)
            ->with(['brand', 'images'])
            ->withCount(['reviews' => fn ($q) => $q->visible()])
            ->withAvg(['reviews' => fn ($q) => $q->visible()], 'rating')
            ->limit(4)
            ->get();

        return inertia('products/show', [
            'product' => ProductDetailResource::make($product)->resolve(),
            'related' => ProductListResource::collection($related)->resolve(),
        ]);
    }

    /** Filter facets scoped to the products in a category. */
    private function facets(Category $category): array
    {
        $productIds = $category->products()->where('is_active', true)->pluck('products.id');

        return [
            'brands' => Brand::query()
                ->whereHas('products', fn ($p) => $p->whereIn('products.id', $productIds))
                ->withCount(['products' => fn ($p) => $p->whereIn('products.id', $productIds)])
                ->orderBy('name')
                ->get(['id', 'name', 'slug'])
                ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'products_count' => $b->products_count]),
            'colors' => Color::query()
                ->whereHas('variants', fn ($v) => $v->whereIn('product_id', $productIds))
                ->orderBy('name')->get(['id', 'name', 'hex_code']),
            'sizes' => Size::query()
                ->whereHas('variants', fn ($v) => $v->whereIn('product_id', $productIds))
                ->orderBy('sort_order')->get(['id', 'label']),
            'price' => [
                'min' => (float) Product::whereIn('id', $productIds)->min('base_price'),
                'max' => (float) Product::whereIn('id', $productIds)->max('base_price'),
            ],
        ];
    }

    /** @return array<int,int> */
    private function ids(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        $value = is_array($value) ? $value : explode(',', (string) $value);

        return collect($value)->map(fn ($v) => (int) trim((string) $v))->filter()->unique()->values()->all();
    }
}
