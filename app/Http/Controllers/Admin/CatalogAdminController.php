<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin catalogue lookups (Figma 08 — Admin · Catalog):
 * brands, categories, and the combined colours & sizes screen.
 *
 * Deletes are guarded: colours and sizes are restrictOnDelete at the database
 * level, so an in-use row is rejected with a field error instead of a 500.
 */
class CatalogAdminController extends Controller
{
    // ---------------- Brands ----------------

    public function brands(): Response
    {
        return Inertia::render('admin/catalog/brands', [
            'brands' => Brand::withCount('products')->orderBy('name')->get()
                ->map(fn (Brand $b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'slug' => $b->slug,
                    'is_active' => (bool) $b->is_active,
                    'products_count' => (int) $b->products_count,
                ])->all(),
        ]);
    }

    public function storeBrand(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $data['slug'] = $this->uniqueSlug(Brand::class, $data['name']);

        Brand::create($data);

        return back()->with('success', 'Brand created.');
    }

    public function updateBrand(Request $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]));

        return back()->with('success', 'Brand updated.');
    }

    public function destroyBrand(Brand $brand): RedirectResponse
    {
        if ($brand->products()->exists()) {
            return back()->withErrors([
                'brand' => 'That brand still has products and cannot be deleted.',
            ]);
        }

        $brand->delete();

        return back()->with('success', 'Brand deleted.');
    }

    // ---------------- Categories ----------------

    public function categories(): Response
    {
        return Inertia::render('admin/catalog/categories', [
            'categories' => Category::with('parent')->withCount('products')->orderBy('name')->get()
                ->map(fn (Category $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'parent' => $c->parent?->name,
                    'parent_id' => $c->parent_id,
                    'products_count' => (int) $c->products_count,
                ])->all(),
            'parents' => Category::whereNull('parent_id')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);
        $data['slug'] = $this->uniqueSlug(Category::class, $data['name']);

        Category::create($data);

        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable', 'integer', 'exists:categories,id',
                // A category cannot be its own parent.
                Rule::notIn([$category->id]),
            ],
        ]);

        $category->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        if ($category->products()->exists() || $category->children()->exists()) {
            return back()->withErrors([
                'category' => 'That category still has products or sub-categories.',
            ]);
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    // ---------------- Colours & sizes ----------------

    public function attributes(): Response
    {
        return Inertia::render('admin/catalog/attributes', [
            'colors' => Color::withCount('variants')->orderBy('name')->get()
                ->map(fn (Color $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'hex_code' => $c->hex_code,
                    'variants_count' => (int) $c->variants_count,
                ])->all(),
            'sizes' => Size::withCount('variants')->orderBy('sort_order')->orderBy('label')->get()
                ->map(fn (Size $s) => [
                    'id' => $s->id,
                    'label' => $s->label,
                    'foot_length_cm' => $s->foot_length_cm !== null ? (float) $s->foot_length_cm : null,
                    'sort_order' => $s->sort_order,
                    'variants_count' => (int) $s->variants_count,
                ])->all(),
        ]);
    }

    public function storeColor(Request $request): RedirectResponse
    {
        Color::create($request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:colors,name'],
            'hex_code' => ['nullable', 'string', 'max:9'],
        ]));

        return back()->with('success', 'Colour added.');
    }

    public function destroyColor(Color $color): RedirectResponse
    {
        if ($color->variants()->exists()) {
            return back()->withErrors([
                'color' => 'That colour is used by existing variants.',
            ]);
        }

        $color->delete();

        return back()->with('success', 'Colour deleted.');
    }

    public function storeSize(Request $request): RedirectResponse
    {
        Size::create($request->validate([
            'label' => ['required', 'string', 'max:32', 'unique:sizes,label'],
            'foot_length_cm' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer'],
        ]));

        return back()->with('success', 'Size added.');
    }

    public function destroySize(Size $size): RedirectResponse
    {
        if ($size->variants()->exists()) {
            return back()->withErrors([
                'size' => 'That size is used by existing variants.',
            ]);
        }

        $size->delete();

        return back()->with('success', 'Size deleted.');
    }

    /** @param class-string<\Illuminate\Database\Eloquent\Model> $model */
    private function uniqueSlug(string $model, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $n = 2;

        while ($model::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$n}";
            $n++;
        }

        return $slug;
    }
}
