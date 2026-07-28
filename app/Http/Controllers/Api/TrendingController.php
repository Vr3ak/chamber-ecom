<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductListResource;
use App\Models\Trending;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TrendingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $products = Trending::query()
            ->active()
            ->with([
                'product' => fn ($q) => $q->active()
                    ->with(['brand', 'images'])
                    ->withCount(['reviews' => fn ($r) => $r->visible()])
                    ->withAvg(['reviews' => fn ($r) => $r->visible()], 'rating'),
            ])
            ->get()
            ->pluck('product')
            ->filter()
            ->values();

        return ProductListResource::collection($products);
    }
}
