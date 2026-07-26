<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardStats;
use Illuminate\Http\JsonResponse;

/**
 * GET /api/admin/dashboard — the Overview screen's stat tiles, order-status
 * donut, recent orders, and top products.
 *
 * The figures live in AdminDashboardStats so this endpoint and the Inertia
 * admin page can never drift apart.
 */
class DashboardController extends Controller
{
    public function __construct(private readonly AdminDashboardStats $stats)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->stats->all());
    }
}
