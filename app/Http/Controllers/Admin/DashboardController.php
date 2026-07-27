<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardStats;
use Inertia\Inertia;
use Inertia\Response;

/** Admin Overview (Figma node 43:556). */
class DashboardController extends Controller
{
    public function __invoke(AdminDashboardStats $stats): Response
    {
        return Inertia::render('admin/dashboard', $stats->all());
    }
}
