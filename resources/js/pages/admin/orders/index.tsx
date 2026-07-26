import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import {
    Cell,
    EmptyRow,
    money,
    Panel,
    Pill,
    Row,
    Table,
} from '@/components/admin/ui';
import AdminLayout from '@/layouts/admin-layout';

type Order = {
    id: number;
    order_number: string;
    customer_name: string | null;
    customer_email: string | null;
    total: number;
    status: string;
    is_paid: boolean;
    placed_at: string | null;
};

type Props = {
    query: string;
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

const FILTERS = [
    ['', 'All'],
    ['pending', 'Pending'],
    ['paid', 'Paid'],
    ['packed', 'Packed'],
    ['shipped', 'Shipped'],
    ['delivered', 'Delivered'],
    ['cancelled', 'Cancelled'],
] as const;

/** Admin orders table — Figma node 32:705. */
export default function AdminOrders({
    query,
    status,
    orders,
    pagination,
}: Props) {
    const [term, setTerm] = useState(query);

    function search(e: FormEvent) {
        e.preventDefault();
        router.get(
            '/admin/orders',
            { q: term.trim(), status: status ?? undefined },
            { preserveState: true },
        );
    }

    return (
        <AdminLayout title="Orders">
            <div className="mb-5 flex flex-wrap items-center justify-between gap-4">
                <div className="flex flex-wrap gap-2">
                    {FILTERS.map(([value, label]) => {
                        const active = (status ?? '') === value;

                        return (
                            <Link
                                key={value || 'all'}
                                href={`/admin/orders${value ? `?status=${value}` : ''}`}
                                className={`rounded-[6px] border px-3 py-1.5 text-[13px] font-medium transition-colors ${active ? 'border-ink bg-ink text-white' : 'border-line text-ink hover:border-ink'}`}
                            >
                                {label}
                            </Link>
                        );
                    })}
                </div>

                <form onSubmit={search} className="flex gap-2">
                    <input
                        value={term}
                        onChange={(e) => setTerm(e.target.value)}
                        placeholder="Order number, customer…"
                        className="h-10 w-64 rounded-[6px] border border-line bg-transparent px-3 text-sm placeholder:text-slate focus:border-ink focus:outline-none"
                    />
                    <button
                        type="submit"
                        className="rounded-[6px] border border-line px-4 text-sm font-medium transition-colors hover:border-ink"
                    >
                        Search
                    </button>
                </form>
            </div>

            <Panel>
                <Table
                    head={[
                        'Order ID',
                        'Customer',
                        'Total',
                        'Payment',
                        'Status',
                        'Date',
                        '',
                    ]}
                >
                    {orders.length === 0 ? (
                        <EmptyRow colSpan={7} label="No orders found." />
                    ) : (
                        orders.map((o) => (
                            <Row key={o.id}>
                                <Cell className="font-medium">
                                    <Link
                                        href={`/admin/orders/${o.id}`}
                                        className="hover:text-gold"
                                    >
                                        {o.order_number}
                                    </Link>
                                </Cell>
                                <Cell className="text-slate">
                                    <span className="block">
                                        {o.customer_name ?? '—'}
                                    </span>
                                    <span className="block text-xs">
                                        {o.customer_email}
                                    </span>
                                </Cell>
                                <Cell>{money(o.total)}</Cell>
                                <Cell>
                                    <Pill
                                        value={o.is_paid ? 'paid' : 'pending'}
                                    />
                                </Cell>
                                <Cell>
                                    <Pill value={o.status} />
                                </Cell>
                                <Cell className="text-slate">
                                    {o.placed_at ?? '—'}
                                </Cell>
                                <Cell>
                                    <div className="flex justify-end">
                                        <Link
                                            href={`/admin/orders/${o.id}`}
                                            className="font-medium hover:text-gold"
                                        >
                                            Manage
                                        </Link>
                                    </div>
                                </Cell>
                            </Row>
                        ))
                    )}
                </Table>
            </Panel>

            {pagination.last > 1 && (
                <div className="mt-5 flex items-center justify-between text-sm">
                    <p className="text-slate">
                        Showing {pagination.from ?? 0}–{pagination.to ?? 0} of{' '}
                        {pagination.total}
                    </p>
                    <div className="flex gap-4">
                        {pagination.current > 1 && (
                            <Link
                                href={`/admin/orders?page=${pagination.current - 1}`}
                                className="text-slate hover:text-gold"
                            >
                                ‹ Prev
                            </Link>
                        )}
                        {pagination.current < pagination.last && (
                            <Link
                                href={`/admin/orders?page=${pagination.current + 1}`}
                                className="text-slate hover:text-gold"
                            >
                                Next ›
                            </Link>
                        )}
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
