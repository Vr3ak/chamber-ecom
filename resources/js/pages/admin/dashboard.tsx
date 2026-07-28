import { Link } from '@inertiajs/react';
import {
    Cell,
    EmptyRow,
    money,
    Panel,
    Pill,
    Row,
    StatCard,
    Table,
} from '@/components/admin/ui';
import AdminLayout from '@/layouts/admin-layout';

type Props = {
    total_revenue: number;
    total_orders: number;
    total_customers: number;
    total_products: number;
    low_stock_count: number;
    order_status: {
        delivered: number;
        shipped: number;
        processing: number;
        cancelled: number;
    };
    recent_orders: {
        id: number;
        order_number: string;
        customer_name: string | null;
        total: number;
        status: string;
        placed_at: string | null;
    }[];
    top_products: {
        product_id: number;
        name: string | null;
        units_sold: number;
        revenue: number;
    }[];
};

const STATUS_COLORS: Record<string, string> = {
    delivered: '#46a344',
    shipped: '#1c4e8a',
    processing: '#ffd369',
    cancelled: '#dc3232',
};

export default function AdminDashboard(props: Props) {
    const status = props.order_status;
    const entries = Object.entries(status) as [keyof typeof status, number][];
    const totalStatus = entries.reduce((sum, [, n]) => sum + n, 0);

    let cursor = 0;
    const slices = entries.map(([key, count]) => {
        const start = totalStatus ? (cursor / totalStatus) * 360 : 0;
        cursor += count;
        const end = totalStatus ? (cursor / totalStatus) * 360 : 0;

        return `${STATUS_COLORS[key]} ${start}deg ${end}deg`;
    });

    return (
        <AdminLayout title="Overview">
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    label="Total Revenue"
                    value={money(props.total_revenue)}
                    hint="Succeeded payments"
                />
                <StatCard label="Total Orders" value={props.total_orders} />
                <StatCard
                    label="Total Customers"
                    value={props.total_customers}
                />
                <StatCard
                    label="Products"
                    value={props.total_products}
                    hint={`${props.low_stock_count} low stock`}
                />
            </div>

            <div className="mt-5 grid gap-5 xl:grid-cols-[2fr_1fr]">
                <Panel
                    title="Recent Orders"
                    action={
                        <Link
                            href="/admin/orders"
                            className="text-[13px] font-medium text-slate hover:text-gold"
                        >
                            View all →
                        </Link>
                    }
                >
                    <Table
                        head={[
                            'Order ID',
                            'Customer',
                            'Total',
                            'Status',
                            'Date',
                        ]}
                    >
                        {props.recent_orders.length === 0 ? (
                            <EmptyRow colSpan={5} label="No orders yet." />
                        ) : (
                            props.recent_orders.map((o) => (
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
                                        {o.customer_name ?? '—'}
                                    </Cell>
                                    <Cell className="text-slate">
                                        {money(o.total)}
                                    </Cell>
                                    <Cell>
                                        <Pill value={o.status} />
                                    </Cell>
                                    <Cell className="text-slate">
                                        {o.placed_at ?? '—'}
                                    </Cell>
                                </Row>
                            ))
                        )}
                    </Table>
                </Panel>

                <Panel title="Order Status">
                    <div className="flex items-center gap-6 p-5">
                        {totalStatus > 0 ? (
                            <div
                                className="h-[130px] w-[130px] shrink-0 rounded-full"
                                style={{
                                    background: `conic-gradient(${slices.join(', ')})`,
                                    mask: 'radial-gradient(circle, transparent 55%, black 56%)',
                                    WebkitMask:
                                        'radial-gradient(circle, transparent 55%, black 56%)',
                                }}
                                role="img"
                                aria-label="Order status breakdown"
                            />
                        ) : (
                            <div className="h-[130px] w-[130px] shrink-0 rounded-full border-[18px] border-line" />
                        )}

                        <ul className="flex flex-col gap-3">
                            {entries.map(([key, count]) => (
                                <li
                                    key={key}
                                    className="flex items-center gap-2 text-[13px]"
                                >
                                    <span
                                        className="h-2 w-2 rounded-full"
                                        style={{
                                            backgroundColor: STATUS_COLORS[key],
                                        }}
                                    />
                                    <span className="text-slate capitalize">
                                        {key}
                                    </span>
                                    <span className="font-medium">{count}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </Panel>
            </div>

            <div className="mt-5">
                <Panel title="Top Products">
                    <Table head={['Product', 'Units sold', 'Revenue']}>
                        {props.top_products.length === 0 ? (
                            <EmptyRow colSpan={3} label="No sales yet." />
                        ) : (
                            props.top_products.map((p) => (
                                <Row key={p.product_id}>
                                    <Cell className="font-medium">
                                        {p.name ?? '—'}
                                    </Cell>
                                    <Cell className="text-slate">
                                        {p.units_sold} sold
                                    </Cell>
                                    <Cell className="font-semibold">
                                        {money(p.revenue)}
                                    </Cell>
                                </Row>
                            ))
                        )}
                    </Table>
                </Panel>
            </div>
        </AdminLayout>
    );
}
