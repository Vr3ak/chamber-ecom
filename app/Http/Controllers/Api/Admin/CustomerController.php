<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 *   GET  /api/admin/customers                 list (+ stats), filter/search
 *   GET  /api/admin/customers/{user}           profile + recent orders
 *   POST /api/admin/customers/{user}/suspend
 *   POST /api/admin/customers/{user}/activate
 *   GET  /api/admin/customers/export           CSV
 */
class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = User::query()
            ->where('is_admin', false)
            ->withCount('orders')
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('email', 'like', '%'.$request->string('search').'%')))
            ->when($request->input('status') === 'active', fn ($q) => $q->where('is_suspended', false))
            ->when($request->input('status') === 'suspended', fn ($q) => $q->where('is_suspended', true))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return CustomerResource::collection($customers)
            ->additional(['stats' => $this->stats()])
            ->response();
    }

    public function show(User $user): CustomerResource
    {
        return CustomerResource::make(
            $user->loadCount('orders')->load(['orders' => fn ($q) => $q->latest()->limit(20)])
        );
    }

    public function suspend(User $user): CustomerResource
    {
        $user->is_suspended = true;
        $user->save();

        return CustomerResource::make($user->loadCount('orders'));
    }

    public function activate(User $user): CustomerResource
    {
        $user->is_suspended = false;
        $user->save();

        return CustomerResource::make($user->loadCount('orders'));
    }

    public function export(): StreamedResponse
    {
        $customers = User::query()->where('is_admin', false)->withCount('orders')->orderBy('name')->get();

        return response()->streamDownload(function () use ($customers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Name', 'Email', 'Phone', 'Orders', 'Joined', 'Status']);

            foreach ($customers as $customer) {
                fputcsv($out, [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    $customer->orders_count,
                    $customer->created_at?->toDateString(),
                    $customer->is_suspended ? 'Suspended' : 'Active',
                ]);
            }

            fclose($out);
        }, 'customers.csv', ['Content-Type' => 'text/csv']);
    }

    /** @return array<string, int> */
    private function stats(): array
    {
        $base = User::query()->where('is_admin', false);

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('is_suspended', false)->count(),
            'suspended' => (clone $base)->where('is_suspended', true)->count(),
            'new_this_month' => (clone $base)->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];
    }
}
