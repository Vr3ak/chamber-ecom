import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Check } from 'lucide-react';
import { useState } from 'react';
import SiteFooter from '@/components/site-footer';
import SiteNavbar from '@/components/site-navbar';
import WriteReviewModal from '@/components/write-review-modal';

type Reviewable = {
    order_item_id: number;
    product_id: number;
    product_name: string;
};

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
    };
    timeline: {
        stage: string;
        note: string | null;
        time: string | null;
    }[];
    reviewable: Reviewable[];
};

const money = (n: number) =>
    n.toLocaleString(undefined, { style: 'currency', currency: 'USD' });

export default function OrderDetail({ order, timeline, reviewable }: Props) {
    const [reviewing, setReviewing] = useState<Reviewable | null>(null);

    return (
        <div className="flex min-h-screen flex-col bg-mist font-display text-ink">
            <Head title={`${order.order_number} — Chamber`} />
            <SiteNavbar variant="dark" />

            <main className="mx-auto w-full max-w-shell flex-1 px-6 pt-10 pb-16 lg:px-16">
                <Link
                    href="/orders"
                    className="inline-flex items-center gap-1.5 text-sm text-slate transition-colors hover:text-gold"
                >
                    <ArrowLeft className="h-4 w-4" />
                    All orders
                </Link>

                <div className="mt-5 flex flex-wrap items-end justify-between gap-4">
                    <div className="flex flex-col gap-1">
                        <h1 className="text-2xl font-semibold">
                            {order.order_number}
                        </h1>
                        <p className="text-sm text-slate">
                            Placed {order.placed_at ?? '—'} ·{' '}
                            {order.is_paid ? 'Paid' : 'Unpaid'}
                        </p>
                    </div>
                    {!order.is_paid && order.status !== 'cancelled' && (
                        <Link
                            href={`/orders/${order.id}/pay`}
                            className="rounded-[6px] bg-gold px-6 py-3 text-[15px] font-medium text-ink transition-opacity hover:opacity-90"
                        >
                            Pay now
                        </Link>
                    )}
                </div>

                <div className="mt-6 flex flex-col gap-8 lg:flex-row">
                    <div className="flex flex-1 flex-col gap-5">
                        {/* Items */}
                        <section className="overflow-hidden rounded-lg border border-line">
                            <h2 className="border-b border-line px-5 py-4 text-base font-semibold">
                                Items
                            </h2>
                            {order.items.map((item, i) => (
                                <div
                                    key={item.id}
                                    className={`flex items-center gap-4 px-5 py-4 ${i < order.items.length - 1 ? 'border-b border-line' : ''}`}
                                >
                                    <div className="flex min-w-0 flex-1 flex-col gap-1">
                                        <span className="text-[15px] font-medium">
                                            {item.product_name}
                                        </span>
                                        <span className="text-[13px] text-slate">
                                            {item.variant_label} · Qty{' '}
                                            {item.quantity}
                                        </span>
                                    </div>

                                    {reviewable.some(
                                        (r) => r.order_item_id === item.id,
                                    ) && (
                                        <button
                                            onClick={() =>
                                                setReviewing(
                                                    reviewable.find(
                                                        (r) =>
                                                            r.order_item_id ===
                                                            item.id,
                                                    )!,
                                                )
                                            }
                                            className="rounded-[6px] border border-line px-3 py-1.5 text-[13px] font-medium transition-colors hover:border-ink"
                                        >
                                            Write a review
                                        </button>
                                    )}

                                    <span className="w-24 text-right text-[15px] font-medium">
                                        {money(item.line_total)}
                                    </span>
                                </div>
                            ))}
                        </section>

                        {/* Fulfilment timeline */}
                        <section className="rounded-lg border border-line p-6">
                            <h2 className="mb-4 text-base font-semibold">
                                Fulfilment timeline
                            </h2>
                            {timeline.length === 0 ? (
                                <p className="text-sm text-slate">
                                    No tracking updates yet.
                                </p>
                            ) : (
                                <ol>
                                    {timeline.map((row, i) => (
                                        <li
                                            key={`${row.stage}-${i}`}
                                            className="flex gap-4 pb-6 last:pb-0"
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
                        </section>
                    </div>

                    {/* Summary + shipping */}
                    <aside className="flex w-full shrink-0 flex-col gap-5 self-start lg:w-[380px]">
                        <section className="flex flex-col gap-3 rounded-lg border border-line p-6">
                            <h2 className="text-base font-semibold">Summary</h2>
                            <div className="flex justify-between text-sm">
                                <span className="text-slate">Subtotal</span>
                                <span>{money(order.subtotal)}</span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-slate">Shipping</span>
                                <span>Free</span>
                            </div>
                            <div className="h-px bg-line" />
                            <div className="flex items-center justify-between">
                                <span className="text-base font-semibold">
                                    Total
                                </span>
                                <span className="text-lg font-semibold">
                                    {money(order.total)}
                                </span>
                            </div>
                        </section>

                        {order.shipping.address && (
                            <section className="flex flex-col gap-2 rounded-lg border border-line p-6 text-sm">
                                <h2 className="text-base font-semibold">
                                    Shipping
                                </h2>
                                <p className="font-medium">
                                    {order.shipping.name}
                                </p>
                                <p className="text-slate">
                                    {order.shipping.address}
                                </p>
                                {order.shipping.phone && (
                                    <p className="text-slate">
                                        {order.shipping.phone}
                                    </p>
                                )}
                                {order.tracking_number && (
                                    <p className="text-slate">
                                        Tracking #{order.tracking_number}
                                    </p>
                                )}
                            </section>
                        )}
                    </aside>
                </div>
            </main>

            {/* Keyed by product so the form remounts per shoe: useForm only
                reads its initial data once, and this modal is mounted with
                the page while `reviewing` is still null. Without the key the
                submitted product_id stays 0 and the review silently fails. */}
            <WriteReviewModal
                key={reviewing?.product_id ?? 'none'}
                orderId={order.id}
                product={reviewing}
                onClose={() => setReviewing(null)}
            />

            <SiteFooter />
        </div>
    );
}
