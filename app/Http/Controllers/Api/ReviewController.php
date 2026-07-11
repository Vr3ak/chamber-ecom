<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * Reviews for a product — feeds the AVG(rating)/COUNT(*) summary and the
 * review list on the detailed product page.
 *
 *   GET  /api/products/{product}/reviews
 *   POST /api/products/{product}/reviews
 */
class ReviewController extends Controller
{
    public function index(Product $product): AnonymousResourceCollection
    {
        $reviews = $product->reviews()->with('user')->latest()->paginate(10);

        return ReviewResource::collection($reviews);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'rating'   => ['required', 'integer', 'between:1,5'],
            'body'     => ['nullable', 'string', 'max:2000'],
            // In a real app the user comes from auth()->id(); accepted here
            // so the endpoint is usable while customer auth is wired up.
            'user_id'  => ['required', 'integer', Rule::exists('users', 'id')],
            'order_id' => ['nullable', 'integer'],
        ]);

        $review = $product->reviews()->create([
            'user_id'     => $data['user_id'],
            'order_id'    => $data['order_id'] ?? null,
            'rating'      => $data['rating'],
            'body'        => $data['body'] ?? null,
            'is_verified' => false,
        ]);

        return ReviewResource::make($review->load('user'))
            ->response()
            ->setStatusCode(201);
    }
}
