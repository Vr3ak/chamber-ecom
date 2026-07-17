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
            <div
                className="min-h-screen bg-[#eeeeee] text-[#222831]"
                style={{ fontFamily: '"IBM Plex Serif", serif' }}
            >
                <SiteNavbar variant="dark" />

                <main className="mx-auto w-full max-w-[1440px] px-6 pt-10 pb-16 lg:px-16">
                    <h1 className="text-2xl font-semibold">Complete Your Payment</h1>

                    <div className="mt-6 grid gap-8 lg:grid-cols-2">
                        {/* ---- QR card ---- */}
                        <section className="rounded-lg border border-[#e4e4e7] px-6 py-10">
                            <h2 className="text-lg font-semibold">Scan to Pay with KHQR</h2>
                            <p className="mt-3 text-sm text-[#393e46]">
                                Open your banking app and scan the code below
                            </p>

                            <div className="mt-6 flex flex-col items-center gap-3 rounded-lg border border-[#e4e4e7] bg-white px-6 py-10">
                                <img
                                    src={qrImage}
                                    alt={`KHQR code for order ${order.order_number}`}
                                    className="h-[200px] w-[200px]"
                                />
                                <p className="text-xs text-[#393e46]">
                                    {merchant} · {money(order.total)}
                                </p>
                            </div>

                            <p className="mt-6 text-center text-xs text-[#6b7280]">
                                This is a real Bakong KHQR payload. Settlement is simulated —
                                confirming below marks the order paid.
                            </p>

                            <button
                                type="button"
                                onClick={confirm}
                                disabled={processing}
                                className="mt-6 w-full rounded-md bg-[#ffd369] py-3 text-sm font-semibold text-[#222831] transition-opacity hover:opacity-90 disabled:opacity-50"
                            >
                                {processing ? 'Confirming…' : "I've completed the payment"}
                            </button>

                            {errors.payment && (
                                <p className="mt-3 text-center text-sm text-[#dc2626]">
                                    {errors.payment}
                                </p>
                            )}
                        </section>

                        {/* ---- summary + status ---- */}
                        <div className="flex flex-col gap-5">
                            <section className="rounded-lg border border-[#e4e4e7] p-6">
                                <h2 className="font-semibold">Order Summary</h2>
                                <dl className="mt-4 space-y-3 text-sm">
                                    <div className="flex justify-between">
                                        <dt className="text-[#393e46]">Order</dt>
                                        <dd>{order.order_number}</dd>
                                    </div>
                                    <div className="flex justify-between">
                                        <dt className="text-[#393e46]">Subtotal</dt>
                                        <dd>{money(order.subtotal)}</dd>
                                    </div>
                                </dl>
                                <div className="mt-4 flex justify-between border-t border-[#e4e4e7] pt-4">
                                    <span className="font-semibold">Total</span>
                                    <span className="text-xl font-bold">{money(order.total)}</span>
                                </div>
                            </section>

                            <section className="rounded-lg border border-[#e4e4e7] p-6">
                                <h2 className="font-semibold">Payment Status</h2>
                                <div className="mt-4 flex items-baseline justify-between gap-4 text-sm">
                                    <span className="text-[#393e46]">Transaction Ref</span>
                                    <span className="truncate font-mono text-xs">
                                        {payment.transaction_ref}
                                    </span>
                                </div>
                                <div className="mt-4 h-1.5 overflow-hidden rounded-full bg-[#dcdcdc]">
                                    <div className="h-full w-1/3 rounded-full bg-[#4b9b6e]" />
                                </div>
                                <p className="mt-3 text-sm text-[#393e46]">
                                    Waiting for payment. Once confirmed, your order moves to
                                    tracking.
                                </p>
                            </section>
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
}
