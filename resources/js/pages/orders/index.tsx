import { Head, Link } from '@inertiajs/react';
import SiteFooter from '@/components/site-footer';
import SiteNavbar from '@/components/site-navbar';

type Order = {
    id: number;
    order_number: string;
    status: string;
    total: number;
    is_paid: boolean;
    placed_at: string | null;
    items: { id: number; product_name: string; quantity: number }[];
};

type Props = {
    status: string | null;
    orders: Order[];
    pagination: {
        current: number;
        last: number;
        total: number;
        from: number | null;
        to: number | null;
    };
};

const TABS = [
    ['', 'All Orders'],
    ['processing', 'Processing'],
    ['shipped', 'Shipped'],
    ['delivered', 'Delivered'],
    ['cancelled', 'Cancelled'],
] as const;

const money = (n: number) =>
    n.toLocaleString(undefined, { style: 'currency', currency: 'USD' });

const STATUS_TONE: Record<string, string> = {
    pending: 'bg-line text-slate',
    paid: 'bg-[#c3ddc5] text-[#1f5b1e]',
    shipped: 'bg-[#d6e4f7] text-[#1c4e8a]',
    delivered: 'bg-[#c3ddc5] text-[#1f5b1e]',
    cancelled: 'bg-[#f7d6d6] text-[#8a1c1c]',
};

export default function OrderHistory({ status, orders, pagination }: Props) {
    const tabHref = (value: string) =>
        value ? `/orders?status=${value}` : '/orders';
    const pageHref = (page: number) =>
        status
            ? `/orders?status=${status}&page=${page}`
            : `/orders?page=${page}`;

    return (
        <div className="flex min-h-screen flex-col bg-mist font-display text-ink">
            <Head title="My Orders — Chamber" />
            <SiteNavbar variant="dark" />

            <main className="mx-auto w-full max-w-shell flex-1 px-6 pt-10 pb-16 lg:px-16">
                <div className="flex flex-col gap-1">
                    <h1 className="text-2xl font-semibold">My Orders</h1>
                    <p className="text-sm text-slate">
                        {pagination.total}{' '}
                        {pagination.total === 1 ? 'order' : 'orders'}
                    </p>
                </div>

                <nav
                    className="mt-6 flex flex-wrap gap-6 border-b border-line"
                    aria-label="Filter orders by status"
                >
                    {TABS.map(([value, label]) => {
                        const on = (status ?? '') === value;

                        return (
                            <Link
                                key={value || 'all'}
                                href={tabHref(value)}
                                aria-current={on ? 'page' : undefined}
                                className={`-mb-px border-b-2 pb-3 text-sm transition-colors ${
                                    on
                                        ? 'border-gold font-medium text-ink'
                                        : 'border-transparent text-slate hover:text-ink'
                                }`}
                            >
                                {label}
                            </Link>
                        );
                    })}
                </nav>

                {orders.length === 0 ? (
                    <div className="mt-6 rounded-lg border border-line p-12 text-center">
                        <p className="text-slate">
                            {status
                                ? 'No orders in this status.'
                                : "You haven't placed any orders yet."}
                        </p>
                        <Link
                            href="/men"
                            className="mt-4 inline-flex rounded-[6px] bg-gold px-6 py-3 text-[15px] font-medium text-ink transition-opacity hover:opacity-90"
                        >
                            Start shopping
                        </Link>
                    </div>
                ) : (
                    <div className="mt-6 overflow-hidden rounded-lg border border-line">
                        {orders.map((order, i) => (
                            <div
                                key={order.id}
                                className={`flex flex-wrap items-center gap-4 px-5 py-4 ${i < orders.length - 1 ? 'border-b border-line' : ''}`}
                            >
                                <div className="flex min-w-0 flex-1 flex-col gap-1">
                                    <Link
                                        href={`/orders/${order.id}`}
                                        className="text-[15px] font-medium hover:text-gold"
                                    >
                                        {order.order_number}
                                    </Link>
                                    <p className="text-[13px] text-slate">
                                        {order.placed_at ?? '—'} ·{' '}
                                        {order.items.length}{' '}
                                        {order.items.length === 1
                                            ? 'item'
                                            : 'items'}
                                    </p>
                                </div>

                                <span
                                    className={`rounded-full px-3 py-1 text-xs font-medium capitalize ${STATUS_TONE[order.status] ?? 'bg-line text-slate'}`}
                                >
                                    {order.status}
                                </span>

                                <span className="w-24 text-right text-[15px] font-medium">
                                    {money(order.total)}
                                </span>

                                <Link
                                    href={`/orders/${order.id}`}
                                    className="text-[13px] font-medium text-ink underline hover:text-gold"
                                >
                                    View
                                </Link>
                            </div>
                        ))}
                    </div>
                )}

                {pagination.last > 1 && (
                    <div className="mt-6 flex items-center justify-between text-sm">
                        <p className="text-slate">
                            Showing {pagination.from ?? 0}–{pagination.to ?? 0}{' '}
                            of {pagination.total}
                        </p>
                        <div className="flex items-center gap-4">
                            {pagination.current > 1 && (
                                <Link
                                    href={pageHref(pagination.current - 1)}
                                    className="text-slate hover:text-gold"
                                >
                                    ‹ Prev
                                </Link>
                            )}
                            {pagination.current < pagination.last && (
                                <Link
                                    href={pageHref(pagination.current + 1)}
                                    className="text-slate hover:text-gold"
                                >
                                    Next ›
                                </Link>
                            )}
                        </div>
                    </div>
                )}
            </main>

            <SiteFooter />
        </div>
    );
}
