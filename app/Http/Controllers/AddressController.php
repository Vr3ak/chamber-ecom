<?php

namespace App\Http\Controllers;

use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * The logged-in customer's saved address book. Session-guarded, same
 * ownership rules as CartController/WishlistController.
 *
 *   GET    /addresses
 *   POST   /addresses
 *   PUT    /addresses/{address}
 *   DELETE /addresses/{address}
 *   POST   /addresses/{address}/default
 */
class AddressController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return AddressResource::collection(
            $request->user()->addresses()->orderByDesc('is_default')->get()
        );
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $data['is_default'] = ($data['is_default'] ?? false) || $user->addresses()->doesntExist();

        $address = DB::transaction(function () use ($user, $data) {
            if ($data['is_default']) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create($data);
        });

        return AddressResource::make($address)->response()->setStatusCode(201);
    }

    public function update(UpdateAddressRequest $request, Address $address): AddressResource
    {
        $this->authorizeOwner($request, $address);

        $data = $request->validated();

        DB::transaction(function () use ($address, $data) {
            if ($data['is_default'] ?? false) {
                $address->user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update($data);
        });

        return AddressResource::make($address->fresh());
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwner($request, $address);

        $address->delete();

        return response()->json(null, 204);
    }

    public function makeDefault(Request $request, Address $address): AddressResource
    {
        $this->authorizeOwner($request, $address);

        DB::transaction(function () use ($address) {
            $address->user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return AddressResource::make($address->fresh());
    }

    private function authorizeOwner(Request $request, Address $address): void
    {
        abort_if($address->user_id !== $request->user()->id, 404);
    }
}
