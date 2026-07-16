import { FormEvent, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    Check,
    CreditCard,
    Mail,
    MapPin,
    Package,
    Search,
    Truck,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';

type TimelineNotification = {
    channel: string;
    type: string;
    status: string;
    sent_at: string | null;
};

type TimelineRow = {
    stage: string;
    note: string | null;
    time: string | null;
    notification?: TimelineNotification;
};

type OrderItem = {
    id: number;
    product_name: string;
    variant_label: string;
    unit_price: number;
    quantity: number;
    line_total: number;
};

type Order = {
    order_number: string;
    status: string;
    total: number;
    is_paid: boolean;
    tracking_number: string | null;
    placed_at: string | null;
    shipping: {
        name: string | null;
        phone: string | null;
        address: string | null;
    };
    items: OrderItem[];
};

type Props = {
    query: string;
    notFound: boolean;
    order: Order | null;
    timeline: TimelineRow[];
};

const STAGE_META: Record<string, { label: string; icon: typeof Check }> = {
    pending: { label: 'Order placed', icon: Package },
    paid: { label: 'Payment confirmed', icon: CreditCard },
    packed: { label: 'Packed', icon: Package },
    shipped: { label: 'Shipped', icon: Truck },
    delivered: { label: 'Delivered', icon: Check },
    cancelled: { label: 'Cancelled', icon: Check },
};

function formatTime(value: string | null): string {
    if (!value) return '';
    const d = new Date(value.replace(' ', 'T'));
    return Number.isNaN(d.getTime())
        ? value
        : d.toLocaleString(undefined, {
              dateStyle: 'medium',
              timeStyle: 'short',
          });
}

export default function Track({ query, notFound, order, timeline }: Props) {
    const [value, setValue] = useState(query ?? '');

    function submit(e: FormEvent) {
        e.preventDefault();
        router.get('/track', { order: value.trim() }, { preserveState: true });
    }

    return (
        <>
            <Head title="Track your order — Chamber" />
            <div className="min-h-screen bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
                <header className="mx-auto flex w-full max-w-3xl items-center justify-between px-6 py-5">
                    <Link href="/" className="text-lg font-bold tracking-tight">
                        Chamber
                    </Link>
                    <Link
                        href="/"
                        className="inline-flex items-center gap-1.5 text-sm text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-white"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Back to store
                    </Link>
                </header>

                <main className="mx-auto w-full max-w-3xl px-6 pb-24">
                    <h1 className="mt-6 text-3xl font-bold tracking-tight">
                        Track your order
                    </h1>
                    <p className="mt-2 text-[#706f6c] dark:text-[#A1A09A]">
                        Enter your order number (e.g.{' '}
                        <code className="rounded bg-black/5 px-1.5 py-0.5 text-sm dark:bg-white/10">
                            CH-2026-0001
                        </code>
                        ) to see its fulfilment timeline.
                    </p>

                    <form onSubmit={submit} className="mt-6 flex gap-2">
                        <Input
                            value={value}
                            onChange={(e) => setValue(e.target.value)}
                            placeholder="Order number"
                            className="flex-1"
                            autoFocus
                        />
                        <Button type="submit">
                            <Search className="h-4 w-4" />
                            Track
                        </Button>
                    </form>

                    {notFound && (
                        <div className="mt-6 rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive">
                            No order found for “{query}”. Check the number and
                            try again.
                        </div>
                    )}

                    {order && (
                        <div className="mt-8 space-y-6">
                            {/* Summary */}
                            <section className="rounded-xl border border-black/10 bg-white p-5 dark:border-white/10 dark:bg-[#161615]">
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p className="text-xs font-medium tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]">
                                            Order
                                        </p>
                                        <p className="text-xl font-bold">
                                            {order.order_number}
                                        </p>
                                        {order.placed_at && (
                                            <p className="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                                Placed{' '}
                                                {formatTime(order.placed_at)}
                                            </p>
                                        )}
                                    </div>
                                    <div className="text-right">
                                        <Badge
                                            variant={
                                                order.status === 'cancelled'
                                                    ? 'destructive'
                                                    : 'secondary'
                                            }
                                            className="capitalize"
                                        >
                                            {STAGE_META[order.status]?.label ??
                                                order.status}
                                        </Badge>
                                        <p className="mt-2 text-lg font-bold">
                                            ${order.total.toFixed(2)}
                                        </p>
                                        <p className="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                            {order.is_paid ? 'Paid' : 'Unpaid'}
                                        </p>
                                    </div>
                                </div>

                                {order.shipping.address && (
                                    <div className="mt-4 flex items-start gap-2 border-t border-black/5 pt-4 text-sm dark:border-white/5">
                                        <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-[#706f6c] dark:text-[#A1A09A]" />
                                        <span>
                                            <span className="font-medium">
                                                {order.shipping.name}
                                            </span>{' '}
                                            · {order.shipping.address}
                                            {order.tracking_number && (
                                                <span className="mt-0.5 block text-[#706f6c] dark:text-[#A1A09A]">
                                                    Tracking #
                                                    {order.tracking_number}
                                                </span>
                                            )}
                                        </span>
                                    </div>
                                )}

                                {order.items.length > 0 && (
                                    <ul className="mt-4 space-y-2 border-t border-black/5 pt-4 text-sm dark:border-white/5">
                                        {order.items.map((it) => (
                                            <li
                                                key={it.id}
                                                className="flex justify-between gap-3"
                                            >
                                                <span>
                                                    {it.quantity}×{' '}
                                                    {it.product_name}
                                                    <span className="text-[#706f6c] dark:text-[#A1A09A]">
                                                        {' '}
                                                        ({it.variant_label})
                                                    </span>
                                                </span>
                                                <span className="font-medium">
                                                    ${it.line_total.toFixed(2)}
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </section>

                            {/* Timeline */}
                            <section className="rounded-xl border border-black/10 bg-white p-5 dark:border-white/10 dark:bg-[#161615]">
                                <h2 className="mb-4 font-semibold">
                                    Fulfilment timeline
                                </h2>
                                {timeline.length === 0 ? (
                                    <p className="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        No tracking updates yet.
                                    </p>
                                ) : (
                                    <ol className="relative">
                                        {timeline.map((row, i) => {
                                            const meta =
                                                STAGE_META[row.stage] ??
                                                STAGE_META.pending;
                                            const Icon = meta.icon;
                                            const last =
                                                i === timeline.length - 1;
                                            return (
                                                <li
                                                    key={`${row.stage}-${i}`}
                                                    className="flex gap-4 pb-6 last:pb-0"
                                                >
                                                    <div className="flex flex-col items-center">
                                                        <span className="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500 text-white">
                                                            <Icon className="h-4 w-4" />
                                                        </span>
                                                        {!last && (
                                                            <span className="mt-1 w-px grow bg-black/10 dark:bg-white/10" />
                                                        )}
                                                    </div>
                                                    <div className="pb-1">
                                                        <p className="font-medium">
                                                            {meta.label}
                                                        </p>
                                                        {row.note && (
                                                            <p className="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                                                {row.note}
                                                            </p>
                                                        )}
                                                        <p className="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                                            {formatTime(
                                                                row.time,
                                                            )}
                                                        </p>
                                                        {row.notification && (
                                                            <span className="mt-2 inline-flex items-center gap-1.5 rounded-md bg-black/5 px-2 py-1 text-xs text-[#706f6c] dark:bg-white/10 dark:text-[#A1A09A]">
                                                                <Mail className="h-3 w-3" />
                                                                Email{' '}
                                                                {
                                                                    row
                                                                        .notification
                                                                        .status
                                                                }
                                                            </span>
                                                        )}
                                                    </div>
                                                </li>
                                            );
                                        })}
                                    </ol>
                                )}
                            </section>
                        </div>
                    )}
                </main>
            </div>
        </>
    );
}
