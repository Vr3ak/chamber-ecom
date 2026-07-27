import { Link, router } from '@inertiajs/react';
import { Cell, EmptyRow, Panel, Pill, Row, Table } from '@/components/admin/ui';
import AdminLayout from '@/layouts/admin-layout';

type Notification = {
    id: number;
    channel: string;
    type: string;
    recipient: string | null;
    status: string;
    sent_at: string | null;
    order_number: string | null;
    customer_name: string | null;
};

type Props = {
    status: string | null;
    notifications: Notification[];
    pagination: {
        current: number;
        last: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    failed_count: number;
};

const FILTERS = [
    ['', 'All'],
    ['sent', 'Sent'],
    ['queued', 'Queued'],
    ['failed', 'Failed'],
] as const;

/** Admin notification inbox — Figma node 48:2774. */
export default function AdminNotifications({
    status,
    notifications,
    pagination,
    failed_count,
}: Props) {
    return (
        <AdminLayout title="Notifications">
            <div className="mb-5 flex flex-wrap items-center justify-between gap-4">
                <div className="flex flex-wrap gap-2">
                    {FILTERS.map(([value, label]) => {
                        const active = (status ?? '') === value;

                        return (
                            <Link
                                key={value || 'all'}
                                href={`/admin/notifications${value ? `?status=${value}` : ''}`}
                                className={`rounded-[6px] border px-3 py-1.5 text-[13px] font-medium transition-colors ${active ? 'border-ink bg-ink text-white' : 'border-line text-ink hover:border-ink'}`}
                            >
                                {label}
                            </Link>
                        );
                    })}
                </div>

                {failed_count > 0 && (
                    <p className="text-[13px] text-[#dc3232]">
                        {failed_count} failed{' '}
                        {failed_count === 1 ? 'delivery' : 'deliveries'}
                    </p>
                )}
            </div>

            <Panel>
                <Table
                    head={[
                        'Type',
                        'Order',
                        'Recipient',
                        'Channel',
                        'Status',
                        'Sent',
                        '',
                    ]}
                >
                    {notifications.length === 0 ? (
                        <EmptyRow colSpan={7} label="Nothing here." />
                    ) : (
                        notifications.map((n) => (
                            <Row key={n.id}>
                                <Cell className="font-medium">{n.type}</Cell>
                                <Cell className="text-slate">
                                    {n.order_number ?? '—'}
                                </Cell>
                                <Cell className="text-slate">
                                    <span className="block">
                                        {n.recipient ?? '—'}
                                    </span>
                                    <span className="block text-xs">
                                        {n.customer_name}
                                    </span>
                                </Cell>
                                <Cell className="text-slate">{n.channel}</Cell>
                                <Cell>
                                    <Pill value={n.status} />
                                </Cell>
                                <Cell className="text-slate">
                                    {n.sent_at ?? '—'}
                                </Cell>
                                <Cell>
                                    <div className="flex justify-end">
                                        <button
                                            onClick={() =>
                                                router.post(
                                                    `/admin/notifications/${n.id}/resend`,
                                                    {},
                                                    { preserveScroll: true },
                                                )
                                            }
                                            disabled={!n.order_number}
                                            className="font-medium hover:text-gold disabled:opacity-40"
                                        >
                                            Resend
                                        </button>
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
                                href={`/admin/notifications?page=${pagination.current - 1}`}
                                className="text-slate hover:text-gold"
                            >
                                ‹ Prev
                            </Link>
                        )}
                        {pagination.current < pagination.last && (
                            <Link
                                href={`/admin/notifications?page=${pagination.current + 1}`}
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
