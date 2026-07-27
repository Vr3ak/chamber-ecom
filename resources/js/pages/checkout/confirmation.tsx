import { Head, Link } from '@inertiajs/react';
import { Check } from 'lucide-react';
import SiteFooter from '@/components/site-footer';
import SiteNavbar from '@/components/site-navbar';

type Order = {
    id: number;
    order_number: string;
    status: string;
    total: number;
    is_paid?: boolean;
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
        quantity: number;
        line_total: number;
    }[];
};

const money = (n: number) =>
    n.toLocaleString(undefined, { style: 'currency', currency: 'USD' });

export default function Confirmation({ order }: { order: Order }) {
    return (
        <div className="flex min-h-screen flex-col bg-mist font-display text-ink">
            <Head title={`Order ${order.order_number} — Chamber`} />
            <SiteNavbar variant="dark" />

            <main className="mx-auto w-full max-w-3xl flex-1 px-6 pt-12 pb-16">
                {/* Confirmation hero — Figma node 48:3502 */}
                <div className="flex flex-col items-center gap-4 text-center">
                    <span className="flex h-16 w-16 items-center justify-center rounded-full bg-[#46a344] text-white">
                        <Check className="h-8 w-8" />
                    </span>
                    <h1 className="text-2xl font-semibold">
                        Thank you for your order
                    </h1>
                    <p className="text-sm text-slate">
                        Your order{' '}
                        <span className="font-medium text-ink">
                            {order.order_number}
                        </span>{' '}
                        has been placed. We'll email you when it ships.
                    </p>
                </div>

                <section className="mt-8 flex flex-col gap-4 rounded-lg border border-line p-6">
                    <div className="flex items-center justify-between">
                        <h2 className="text-base font-semibold">
                            Order Summary
                        </h2>
                        <span className="rounded-full bg-line px-3 py-1 text-xs font-medium capitalize">
                            {order.status}
                        </span>
                    </div>

                    <ul className="flex flex-col gap-3">
                        {order.items.map((item) => (
                            <li
                                key={item.id}
                                className="flex justify-between gap-4 text-sm"
                            >
                                <span>
                                    {item.quantity}× {item.product_name}
                                    <span className="text-slate">
                                        {' '}
                                        ({item.variant_label})
                                    </span>
                                </span>
                                <span className="font-medium">
                                    {money(item.line_total)}
                                </span>
                            </li>
                        ))}
                    </ul>

                    <div className="h-px bg-line" />
                    <div className="flex items-center justify-between">
                        <span className="text-base font-semibold">Total</span>
                        <span className="text-lg font-semibold">
                            {money(order.total)}
                        </span>
                    </div>
                </section>

                {order.shipping.address && (
                    <section className="mt-5 flex flex-col gap-2 rounded-lg border border-line p-6 text-sm">
                        <h2 className="text-base font-semibold">Shipping to</h2>
                        <p className="font-medium">{order.shipping.name}</p>
                        <p className="text-slate">{order.shipping.address}</p>
                        {order.shipping.phone && (
                            <p className="text-slate">{order.shipping.phone}</p>
                        )}
                    </section>
                )}

                <div className="mt-8 flex flex-wrap justify-center gap-3">
                    <Link
                        href={`/orders/${order.id}`}
                        className="rounded-[6px] bg-gold px-6 py-3 text-[15px] font-medium text-ink transition-opacity hover:opacity-90"
                    >
                        View order
                    </Link>
                    <Link
                        href={`/track?order=${order.order_number}`}
                        className="rounded-[6px] border border-line px-6 py-3 text-[15px] font-medium text-ink transition-colors hover:border-ink"
                    >
                        Track order
                    </Link>
                    <Link
                        href="/men"
                        className="rounded-[6px] border border-line px-6 py-3 text-[15px] font-medium text-ink transition-colors hover:border-ink"
                    >
                        Continue shopping
                    </Link>
                </div>
            </main>

            <SiteFooter />
        </div>
    );
}
