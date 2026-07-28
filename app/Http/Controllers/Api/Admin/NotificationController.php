<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = Notification::query()
            ->with('order')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return NotificationResource::collection($notifications);
    }

    public function failed(): AnonymousResourceCollection
    {
        return NotificationResource::collection(
            Notification::with('order')->where('status', 'failed')->orderByDesc('id')->get()
        );
    }

    public function resend(Notification $notification): JsonResponse
    {
        $order = $notification->order;

        abort_if(! $order, 404, 'This notification has no order to resend against.');

        $fresh = $this->notifications->notify($order, $notification->type);

        return NotificationResource::make($fresh->load('order'))
            ->response()
            ->setStatusCode(201);
    }
}
