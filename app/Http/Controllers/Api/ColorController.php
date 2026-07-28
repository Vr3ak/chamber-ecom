<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreColorRequest;
use App\Http\Requests\Catalog\UpdateColorRequest;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ColorController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ColorResource::collection(Color::orderBy('name')->get());
    }

    public function store(StoreColorRequest $request): JsonResponse
    {
        $color = Color::create($request->validated());

        return ColorResource::make($color)->response()->setStatusCode(201);
    }

    public function update(UpdateColorRequest $request, Color $color): ColorResource
    {
        $color->update($request->validated());

        return ColorResource::make($color->fresh());
    }

    public function destroy(Color $color): JsonResponse
    {
        $color->delete();

        return response()->json(null, 204);
    }
}
