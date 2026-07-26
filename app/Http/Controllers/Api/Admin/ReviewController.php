<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 *   GET  /api/admin/reviews                 cross-product, filterable
 *   POST /api/admin/reviews/{review}/hide
 *   POST /api/admin/reviews/{review}/unhide
 */
class ReviewController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $reviews = Review::query()
            ->with(['product', 'user'])
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->integer('product_id')))
            ->when($request->filled('rating'), fn ($q) => $q->where('rating', $request->integer('rating')))
            ->when($request->filled('verified'), fn ($q) => $q->where('is_verified', $request->boolean('verified')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return ReviewResource::collection($reviews);
    }

    public function hide(Review $review): ReviewResource
    {
        $review->is_hidden = true;
        $review->save();

        return ReviewResource::make($review->load(['product', 'user']));
    }

    public function unhide(Review $review): ReviewResource
    {
        $review->is_hidden = false;
        $review->save();

        return ReviewResource::make($review->load(['product', 'user']));
    }
}
