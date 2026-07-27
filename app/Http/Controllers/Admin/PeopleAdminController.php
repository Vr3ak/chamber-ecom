<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Review;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Admin customers, notifications and review moderation (Figma 09). */
class PeopleAdminController extends Controller
{
    // ---------------- Customers ----------------

    public function customers(Request $request): Response
    {
        $term = trim((string) $request->query('q', ''));

        $customers = User::query()
            ->where('is_admin', false)
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->when($term !== '', function ($q) use ($term) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
                $q->where('name', 'like', $like)->orWhere('email', 'like', $like);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/customers', [
            'query' => $term,
            'customers' => collect($customers->items())->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'is_suspended' => $u->isSuspended(),
                'orders_count' => (int) $u->orders_count,
                'orders_total' => (float) ($u->orders_sum_total ?? 0),
                'joined_at' => $u->created_at?->toDateString(),
            ])->all(),
            'pagination' => [
                'current' => $customers->currentPage(),
                'last' => $customers->lastPage(),
                'total' => $customers->total(),
                'from' => $customers->firstItem(),
                'to' => $customers->lastItem(),
            ],
        ]);
    }

    public function suspendCustomer(User $user): RedirectResponse
    {
        // is_suspended is deliberately not mass-assignable on the model.
        $user->forceFill(['is_suspended' => true])->save();

        return back()->with('success', 'Customer suspended.');
    }

    public function activateCustomer(User $user): RedirectResponse
    {
        $user->forceFill(['is_suspended' => false])->save();

        return back()->with('success', 'Customer reactivated.');
    }

    // ---------------- Notifications ----------------

    public function notifications(Request $request): Response
    {
        $status = $request->query('status');

        $notifications = Notification::query()
            ->with(['order', 'user'])
            ->when($status, fn ($q) => $q->where('status', $status))
            // The table has no timestamps (only sent_at, which is null until
            // delivery), so latest() would order by a created_at that does not
            // exist. Newest-first by id, matching Api\Admin\NotificationController.
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/notifications', [
            'status' => $status,
            'notifications' => collect($notifications->items())->map(fn (Notification $n) => [
                'id' => $n->id,
                'channel' => $n->channel,
                'type' => $n->type,
                'recipient' => $n->recipient,
                'status' => $n->status,
                'sent_at' => $n->sent_at?->toDateTimeString(),
                'order_number' => $n->order?->order_number,
                'customer_name' => $n->user?->name,
            ])->all(),
            'pagination' => [
                'current' => $notifications->currentPage(),
                'last' => $notifications->lastPage(),
                'total' => $notifications->total(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ],
            'failed_count' => Notification::where('status', 'failed')->count(),
        ]);
    }

    /**
     * Re-send by issuing a fresh notification of the same type, matching
     * Api\Admin\NotificationController::resend — the service has no resend();
     * it only knows how to notify(Order, type).
     */
    public function resendNotification(
        Notification $notification,
        NotificationService $notifications,
    ): RedirectResponse {
        $order = $notification->order;

        abort_if(! $order, 404, 'This notification has no order to resend against.');

        $notifications->notify($order, $notification->type);

        return back()->with('success', 'Notification resent.');
    }

    // ---------------- Reviews ----------------

    public function reviews(Request $request): Response
    {
        $visibility = $request->query('visibility');

        $reviews = Review::query()
            ->with(['user', 'product'])
            ->when($visibility === 'hidden', fn ($q) => $q->where('is_hidden', true))
            ->when($visibility === 'visible', fn ($q) => $q->where('is_hidden', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/reviews', [
            'visibility' => $visibility,
            'reviews' => collect($reviews->items())->map(fn (Review $r) => [
                'id' => $r->id,
                'rating' => (int) $r->rating,
                'body' => $r->body,
                'is_hidden' => (bool) $r->is_hidden,
                'is_verified' => (bool) $r->is_verified,
                'author' => $r->user?->name,
                'product' => $r->product?->name,
                'product_slug' => $r->product?->slug,
                'created_at' => $r->created_at?->toDateString(),
            ])->all(),
            'pagination' => [
                'current' => $reviews->currentPage(),
                'last' => $reviews->lastPage(),
                'total' => $reviews->total(),
                'from' => $reviews->firstItem(),
                'to' => $reviews->lastItem(),
            ],
        ]);
    }

    public function hideReview(Review $review): RedirectResponse
    {
        $review->update(['is_hidden' => true]);

        return back()->with('success', 'Review hidden.');
    }

    public function unhideReview(Review $review): RedirectResponse
    {
        $review->update(['is_hidden' => false]);

        return back()->with('success', 'Review restored.');
    }
}
