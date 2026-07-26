<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

/**
 * Superadmin-only management of other admin accounts.
 *
 *   GET  /api/admin/admins  list
 *   POST /api/admin/admins  create
 */
class AdminController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return AdminResource::collection(Admin::query()->orderBy('name')->get());
    }

    public function store(StoreAdminRequest $request): JsonResponse
    {
        $admin = Admin::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => $request->validated('role', 'admin'),
        ]);

        return AdminResource::make($admin)->response()->setStatusCode(201);
    }
}
