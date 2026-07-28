<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardStats;
use Illuminate\Http\JsonResponse;

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
