import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import SiteNavbar from '@/components/site-navbar';
import { confirm as confirmPayment } from '@/routes/checkout';

type Props = {
    order: { order_number: string; subtotal: number; total: number };
    payment: { id: number; transaction_ref: string | null };
    qrImage: string;
    merchant: string;
};

const money = (n: number) =>
    n.toLocaleString(undefined, { style: 'currency', currency: 'USD' });

export default function Payment({ order, payment, qrImage, merchant }: Props) {
    const { errors } = usePage().props;
    const [processing, setProcessing] = useState(false);

    const confirm = () =>
        router.post(
            confirmPayment(payment.id).url,
            {},
            {
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
            },
        );

    return (
        <>
            <Head title={`Pay ${order.order_number} — Chamber`} />
            <div className="font-display min-h-screen bg-mist text-ink">
                <SiteNavbar variant="dark" />

                <main className="mx-auto w-full max-w-shell px-6 pt-10 pb-16 lg:px-16">
                    <h1 className="text-2xl font-semibold">
                        Complete Your Payment
                    </h1>

                    <div className="mt-6 grid gap-8 lg:grid-cols-2">
                        {/* ---- QR card — Figma node 48:2173 ---- */}
                        <section className="flex flex-col gap-6 rounded-lg border border-line px-6 py-10">
                            <h2 className="text-lg font-semibold">
                                Scan to Pay with KHQR
                            </h2>
                            <p className="text-sm text-slate">
                                Open your banking app and scan the code below
                            </p>

                            <div className="flex min-h-[280px] flex-col items-center justify-center gap-3 rounded-xl border border-line">
                                <img
                                    src={qrImage}
                                    alt={`KHQR code for order ${order.order_number}`}
                                    className="h-[204px] w-[204px] rounded-lg"
                                />
                                <p className="text-xs text-slate">
                                    {merchant} · {money(order.total)}
                                </p>
                            </div>

                            <p className="text-center text-xs text-slate">
                                This is a real Bakong KHQR payload. Settlement is
                                simulated — confirming below marks the order paid.
                            </p>

                            <button
                                type="button"
                                onClick={confirm}
                                disabled={processing}
                                className="w-full rounded-[6px] bg-gold py-3 text-[15px] font-medium text-ink transition-opacity hover:opacity-90 disabled:opacity-50"
                            >
                                {processing
                                    ? 'Confirming…'
                                    : "I've completed the payment"}
                            </button>

                            {errors.payment && (
                                <p className="text-center text-sm text-[#dc2626]">
                                    {errors.payment}
                                </p>
                            )}
                        </section>

                        {/* ---- summary + status — Figma node 48:2188 ---- */}
                        <div className="flex flex-col gap-5">
                            <section className="flex flex-col gap-4 rounded-lg border border-line p-6">
                                <h2 className="text-[15px] font-semibold">
                                    Order Summary
                                </h2>
                                <dl className="space-y-3 text-[13px]">
                                    <div className="flex justify-between">
                                        <dt className="text-slate">Order</dt>
                                        <dd>{order.order_number}</dd>
                                    </div>
                                    <div className="flex justify-between">
                                        <dt className="text-slate">Subtotal</dt>
                                        <dd>{money(order.subtotal)}</dd>
                                    </div>
                                </dl>
                                <div className="flex items-center justify-between border-t border-line pt-4">
                                    <span className="text-base font-semibold">
                                        Total
                                    </span>
                                    <span className="text-xl font-bold">
                                        {money(order.total)}
                                    </span>
                                </div>
                            </section>

                            <section className="flex flex-col gap-4 rounded-lg border border-line p-6">
                                <h2 className="text-[15px] font-semibold">
                                    Payment Status
                                </h2>
                                <div className="flex items-baseline justify-between gap-4 text-[13px]">
                                    <span className="text-slate">
                                        Transaction Ref
                                    </span>
                                    <span className="truncate font-mono text-xs">
                                        {payment.transaction_ref}
                                    </span>
                                </div>
                                <div className="h-1.5 overflow-hidden rounded-full bg-[#c3ddc5]">
                                    <div className="h-full w-1/3 rounded-full bg-[#46a344]" />
                                </div>
                                <p className="text-xs text-slate">
                                    We'll confirm your order automatically once
                                    payment is detected.
                                </p>
                            </section>
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
}
