<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

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
            'rating'  => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ]);

        $review = $product->reviews()->create([
            'user_id'     => $data['user_id'],
            'rating'      => $data['rating'],
            'comment'     => $data['comment'] ?? null,
            'is_verified' => false,
        ]);

        return ReviewResource::make($review->load('user'))
            ->response()
            ->setStatusCode(201);
    }
}