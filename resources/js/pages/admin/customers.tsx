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

type Customer = {
    id: number;
    name: string;
    email: string;
    is_suspended: boolean;
    orders_count: number;
    orders_total: number;
    joined_at: string | null;
};

type Props = {
    query: string;
    customers: Customer[];
    pagination: {
        current: number;
        last: number;
        total: number;
        from: number | null;
        to: number | null;
    };
};

export default function AdminCustomers({
    query,
    customers,
    pagination,
}: Props) {
    const [term, setTerm] = useState(query);

    function search(e: FormEvent) {
        e.preventDefault();
        router.get(
            '/admin/customers',
            { q: term.trim() },
            { preserveState: true },
        );
    }

    return (
        <AdminLayout title="Customers">
            <form onSubmit={search} className="mb-5 flex gap-2">
                <input
                    value={term}
                    onChange={(e) => setTerm(e.target.value)}
                    placeholder="Search by name or email…"
                    className="h-10 w-full max-w-sm rounded-[6px] border border-line bg-transparent px-3 text-sm placeholder:text-slate focus:border-ink focus:outline-none"
                />
                <button
                    type="submit"
                    className="rounded-[6px] border border-line px-4 text-sm font-medium transition-colors hover:border-ink"
                >
                    Search
                </button>
            </form>

            <Panel>
                <Table
                    head={[
                        'Customer',
                        'Orders',
                        'Lifetime value',
                        'Joined',
                        'Status',
                        '',
                    ]}
                >
                    {customers.length === 0 ? (
                        <EmptyRow colSpan={6} label="No customers found." />
                    ) : (
                        customers.map((c) => (
                            <Row key={c.id}>
                                <Cell>
                                    <span className="block font-medium">
                                        {c.name}
                                    </span>
                                    <span className="block text-xs text-slate">
                                        {c.email}
                                    </span>
                                </Cell>
                                <Cell className="text-slate">
                                    {c.orders_count}
                                </Cell>
                                <Cell>{money(c.orders_total)}</Cell>
                                <Cell className="text-slate">
                                    {c.joined_at ?? '—'}
                                </Cell>
                                <Cell>
                                    <Pill
                                        value={
                                            c.is_suspended
                                                ? 'suspended'
                                                : 'active'
                                        }
                                    />
                                </Cell>
                                <Cell>
                                    <div className="flex justify-end">
                                        {c.is_suspended ? (
                                            <button
                                                onClick={() =>
                                                    router.post(
                                                        `/admin/customers/${c.id}/activate`,
                                                        {},
                                                        {
                                                            preserveScroll: true,
                                                        },
                                                    )
                                                }
                                                className="font-medium hover:text-gold"
                                            >
                                                Reactivate
                                            </button>
                                        ) : (
                                            <button
                                                onClick={() =>
                                                    router.post(
                                                        `/admin/customers/${c.id}/suspend`,
                                                        {},
                                                        {
                                                            preserveScroll: true,
                                                        },
                                                    )
                                                }
                                                className="text-[#dc3232] hover:underline"
                                            >
                                                Suspend
                                            </button>
                                        )}
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
                                href={`/admin/customers?page=${pagination.current - 1}`}
                                className="text-slate hover:text-gold"
                            >
                                ‹ Prev
                            </Link>
                        )}
                        {pagination.current < pagination.last && (
                            <Link
                                href={`/admin/customers?page=${pagination.current + 1}`}
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
