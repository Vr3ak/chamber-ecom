<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreSizeRequest;
use App\Http\Requests\Catalog\UpdateSizeRequest;
use App\Http\Resources\SizeResource;
use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SizeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SizeResource::collection(Size::orderBy('sort_order')->get());
    }

    public function store(StoreSizeRequest $request): JsonResponse
    {
        $size = Size::create($request->validated());

        return SizeResource::make($size)->response()->setStatusCode(201);
    }

    public function update(UpdateSizeRequest $request, Size $size): SizeResource
    {
        $size->update($request->validated());

        return SizeResource::make($size->fresh());
    }

    public function destroy(Size $size): JsonResponse
    {
        $size->delete();

        return response()->json(null, 204);
    }
}
