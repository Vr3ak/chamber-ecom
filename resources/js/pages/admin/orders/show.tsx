import { Link, useForm } from '@inertiajs/react';
import { Check } from 'lucide-react';
import { Cell, money, Panel, Pill, Row, Table } from '@/components/admin/ui';
import InputError from '@/components/input-error';
import AdminLayout from '@/layouts/admin-layout';

type Props = {
    order: {
        id: number;
        order_number: string;
        status: string;
        subtotal: number;
        total: number;
        is_paid: boolean;
        tracking_number: string | null;
        placed_at: string | null;
        shipping: {
            name: string | null;
            phone: string | null;
            address: string | null;
        };
        items: {
            id: number;
            product_name: string;
            variant_label: string;
            unit_price: number;
            quantity: number;
            line_total: number;
        }[];
        customer?: { id: number; name: string; email: string } | null;
    };
    timeline: { stage: string; note: string | null; time: string | null }[];
    stages: string[];
};

export default function AdminOrderDetail({ order, timeline, stages }: Props) {
    const advance = useForm({ status: '', note: '' });
    const tracking = useForm({ tracking_number: order.tracking_number ?? '' });

    return (
        <AdminLayout
            title={order.order_number}
            actions={
                <Link
                    href="/admin/orders"
                    className="rounded-[6px] border border-fog px-4 py-2 text-[13px] font-medium text-mist transition-colors hover:border-gold hover:text-gold"
                >
                    All orders
                </Link>
            }
        >
            <div className="grid gap-5 xl:grid-cols-[2fr_1fr]">
                <div className="flex flex-col gap-5">
                    <Panel title="Items">
                        <Table head={['Product', 'Variant', 'Qty', 'Total']}>
                            {order.items.map((item) => (
                                <Row key={item.id}>
                                    <Cell className="font-medium">
                                        {item.product_name}
                                    </Cell>
                                    <Cell className="text-slate">
                                        {item.variant_label}
                                    </Cell>
                                    <Cell className="text-slate">
                                        {item.quantity}
                                    </Cell>
                                    <Cell className="font-medium">
                                        {money(item.line_total)}
                                    </Cell>
                                </Row>
                            ))}
                        </Table>
                        <div className="flex items-center justify-between border-t border-line px-5 py-4">
                            <span className="text-base font-semibold">
                                Total
                            </span>
                            <span className="text-lg font-semibold">
                                {money(order.total)}
                            </span>
                        </div>
                    </Panel>

                    <Panel title="Fulfilment timeline">
                        <div className="p-5">
                            {timeline.length === 0 ? (
                                <p className="text-sm text-slate">
                                    No tracking updates yet.
                                </p>
                            ) : (
                                <ol>
                                    {timeline.map((row, i) => (
                                        <li
                                            key={`${row.stage}-${i}`}
                                            className="flex gap-4 pb-5 last:pb-0"
                                        >
                                            <div className="flex flex-col items-center">
                                                <span className="flex h-8 w-8 items-center justify-center rounded-full bg-[#46a344] text-white">
                                                    <Check className="h-4 w-4" />
                                                </span>
                                                {i < timeline.length - 1 && (
                                                    <span className="mt-1 w-px grow bg-line" />
                                                )}
                                            </div>
                                            <div>
                                                <p className="text-sm font-medium capitalize">
                                                    {row.stage}
                                                </p>
                                                {row.note && (
                                                    <p className="text-sm text-slate">
                                                        {row.note}
                                                    </p>
                                                )}
                                                <p className="mt-0.5 text-xs text-slate">
                                                    {row.time ?? '—'}
                                                </p>
                                            </div>
                                        </li>
                                    ))}
                                </ol>
                            )}
                        </div>
                    </Panel>
                </div>

                <div className="flex flex-col gap-5">
                    <Panel title="Status">
                        <div className="flex flex-col gap-4 p-5">
                            <div className="flex items-center gap-2">
                                <Pill value={order.status} />
                                <Pill
                                    value={order.is_paid ? 'paid' : 'pending'}
                                />
                            </div>

                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    advance.post(
                                        `/admin/orders/${order.id}/advance`,
                                        {
                                            preserveScroll: true,
                                            onSuccess: () => advance.reset(),
                                        },
                                    );
                                }}
                                className="flex flex-col gap-3"
                            >
                                <label className="text-[13px] font-medium">
                                    Advance to
                                </label>
                                <select
                                    value={advance.data.status}
                                    onChange={(e) =>
                                        advance.setData(
                                            'status',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    className="h-10 w-full rounded-[6px] border border-line bg-transparent px-3 text-sm focus:border-ink focus:outline-none"
                                >
                                    <option value="">Select a stage…</option>
                                    {stages.map((s) => (
                                        <option key={s} value={s}>
                                            {s}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={advance.errors.status} />

                                <input
                                    value={advance.data.note}
                                    onChange={(e) =>
                                        advance.setData('note', e.target.value)
                                    }
                                    placeholder="Note (optional)"
                                    className="h-10 w-full rounded-[6px] border border-line bg-transparent px-3 text-sm placeholder:text-slate focus:border-ink focus:outline-none"
                                />

                                <button
                                    type="submit"
                                    disabled={advance.processing}
                                    className="h-10 rounded-[6px] bg-gold text-sm font-medium text-ink transition-opacity hover:opacity-90 disabled:opacity-50"
                                >
                                    Update status
                                </button>
                            </form>
                        </div>
                    </Panel>

                    <Panel title="Tracking number">
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                tracking.patch(
                                    `/admin/orders/${order.id}/tracking-number`,
                                    { preserveScroll: true },
                                );
                            }}
                            className="flex flex-col gap-3 p-5"
                        >
                            <input
                                value={tracking.data.tracking_number}
                                onChange={(e) =>
                                    tracking.setData(
                                        'tracking_number',
                                        e.target.value,
                                    )
                                }
                                placeholder="e.g. CH-TRK-00123"
                                className="h-10 w-full rounded-[6px] border border-line bg-transparent px-3 text-sm placeholder:text-slate focus:border-ink focus:outline-none"
                            />
                            <InputError
                                message={tracking.errors.tracking_number}
                            />
                            <button
                                type="submit"
                                disabled={tracking.processing}
                                className="h-10 rounded-[6px] border border-line text-sm font-medium transition-colors hover:border-ink disabled:opacity-50"
                            >
                                Save
                            </button>
                        </form>
                    </Panel>

                    <Panel title="Customer">
                        <div className="flex flex-col gap-1 p-5 text-sm">
                            <p className="font-medium">
                                {order.customer?.name ??
                                    order.shipping.name ??
                                    '—'}
                            </p>
                            {order.customer?.email && (
                                <p className="text-slate">
                                    {order.customer.email}
                                </p>
                            )}
                            {order.shipping.address && (
                                <p className="mt-2 text-slate">
                                    {order.shipping.address}
                                </p>
                            )}
                            {order.shipping.phone && (
                                <p className="text-slate">
                                    {order.shipping.phone}
                                </p>
                            )}
                        </div>
                    </Panel>
                </div>
            </div>
        </AdminLayout>
    );
}
