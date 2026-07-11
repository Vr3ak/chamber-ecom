<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductListResource;
use App\Models\Trending;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Public "Trending" rail — the shoes admins have curated as featured,
 * in display order. Also drives the Trending badge on the product page.
 *
 *   GET /api/trending
 */
class TrendingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $products = Trending::query()
            ->active()
            ->with([
                'product' => fn ($q) => $q->active()
                    ->with(['brand', 'images'])
                    ->withCount('reviews')
                    ->withAvg('reviews', 'rating'),
            ])
            ->get()
            ->pluck('product')
            ->filter()
            ->values();

        return ProductListResource::collection($products);
    }
}
