<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\Trending;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin catalogue management (Figma 06 — Admin · Products).
 *
 * Session-guarded counterpart to Api\ProductController / Api\ProductVariantController,
 * which are Sanctum-token surfaces and unreachable from the browser session.
 */
class ProductAdminController extends Controller
{
    public function index(Request $request): Response
    {
        $term = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->with(['brand', 'trending'])
            ->withCount('variants')
            ->withSum('variants', 'stock_quantity')
            ->when($term !== '', function ($q) use ($term) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
                $q->where('name', 'like', $like)
                    ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', $like));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/products/index', [
            'query' => $term,
            'products' => collect($products->items())->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'brand' => $p->brand?->name,
                'base_price' => (float) $p->base_price,
                'is_active' => (bool) $p->is_active,
                'is_trending' => (bool) $p->trending?->is_active,
                'variants_count' => (int) $p->variants_count,
                'stock' => (int) ($p->variants_sum_stock_quantity ?? 0),
            ])->all(),
            'pagination' => $this->paginationMeta($products),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/form', [
            'product' => null,
            'options' => $this->options(),
        ]);
    }

    public function edit(Product $product): Response
    {
        $product->load(['categories', 'variants.color', 'variants.size', 'images', 'trending']);

        return Inertia::render('admin/products/form', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'base_price' => (float) $product->base_price,
                'brand_id' => $product->brand_id,
                'is_active' => (bool) $product->is_active,
                'category_ids' => $product->categories->pluck('id')->all(),
                'image_url' => $product->images->first()?->url,
                'is_trending' => (bool) $product->trending?->is_active,
            ],
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $product = Product::create(collect($data)->except(['image', 'is_trending'])->all());
        $product->categories()->sync($request->input('category_ids', []));

        if ($request->hasFile('image')) {
            ProductImage::storeUpload($request->file('image'), $product->id, ['is_primary' => true]);
        }

        $this->syncTrending($request, $product);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product created.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        $product->update(collect($data)->except(['image', 'is_trending'])->all());
        $product->categories()->sync($request->input('category_ids', []));

        if ($request->hasFile('image')) {
            $product->images()->where('is_primary', true)->delete();
            ProductImage::storeUpload($request->file('image'), $product->id, ['is_primary' => true]);
        }

        $this->syncTrending($request, $product);

        return back()->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    /** Variant table for one product (Figma node 48:4044). */
    public function variants(Product $product): Response
    {
        $product->load(['variants.color', 'variants.size', 'brand']);

        return Inertia::render('admin/products/variants', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand?->name,
            ],
            'variants' => $product->variants->map(fn (ProductVariant $v) => [
                'id' => $v->id,
                'color' => $v->color?->name,
                'size' => $v->size?->label,
                'price' => $v->price !== null ? (float) $v->price : null,
                'effective_price' => (float) $v->effective_price,
                'stock_quantity' => (int) $v->stock_quantity,
                'status' => $v->status,
            ])->all(),
            'options' => $this->options(),
        ]);
    }

    public function storeVariant(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            // product_variants has a unique (product_id, color_id, size_id)
            // constraint — enforce it here so the user gets a field error
            // rather than a 500 from the database.
            'color_id' => [
                'required', 'integer', 'exists:colors,id',
                Rule::unique('product_variants', 'color_id')
                    ->where('product_id', $product->id)
                    ->where('size_id', $request->integer('size_id')),
            ],
            'size_id' => ['required', 'integer', 'exists:sizes,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ], [
            'color_id.unique' => 'That colour and size combination already exists for this product.',
        ]);

        $product->variants()->create($data);

        return back()->with('success', 'Variant added.');
    }

    public function updateVariant(Request $request, ProductVariant $variant): RedirectResponse
    {
        $data = $request->validate([
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ]);

        $variant->update($data);

        return back()->with('success', 'Variant updated.');
    }

    public function destroyVariant(ProductVariant $variant): RedirectResponse
    {
        $variant->delete();

        return back()->with('success', 'Variant removed.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
            'is_trending' => ['boolean'],
        ]);
    }

    /**
     * Feature (or un-feature) a shoe on the storefront's Trending rail.
     *
     * The row is kept and flipped rather than deleted so a shoe that gets
     * re-featured keeps the position it was curated at. New entries go to the
     * back of the rail. admin_id is best-effort: the panel authenticates a
     * User, and only some of those have a matching row in the `admins` table
     * this column references.
     */
    private function syncTrending(Request $request, Product $product): void
    {
        if (! $request->has('is_trending')) {
            return;
        }

        $featured = $request->boolean('is_trending');
        $existing = $product->trending()->first();

        if (! $featured && ! $existing) {
            return;
        }

        $product->trending()->updateOrCreate([], [
            'is_active' => $featured,
            'sort_order' => $existing->sort_order ?? ((int) Trending::max('sort_order') + 1),
            'admin_id' => $existing->admin_id
                ?? Admin::where('email', $request->user()->email)->value('id'),
        ]);

        $product->unsetRelation('trending');
    }

    /** @return array<string, mixed> */
    private function options(): array
    {
        return [
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'colors' => Color::orderBy('name')->get(['id', 'name', 'hex_code']),
            'sizes' => Size::orderBy('label')->get(['id', 'label']),
        ];
    }

    /** @return array<string, mixed> */
    private function paginationMeta($paginator): array
    {
        return [
            'current' => $paginator->currentPage(),
            'last' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
