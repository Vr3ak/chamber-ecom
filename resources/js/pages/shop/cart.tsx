import { Head, Link, router } from '@inertiajs/react';
import { Minus, Plus, X } from 'lucide-react';
import { tileGradient } from '@/components/product-card';
import SiteFooter from '@/components/site-footer';
import SiteNavbar from '@/components/site-navbar';
import { clear } from '@/routes/cart';
import items from '@/routes/cart/items';

export type CartItem = {
    id: number;
    quantity: number;
    unit_price: number;
    line_total: number;
    insufficient_stock?: boolean;
    variant: {
        id: number;
        color?: { id: number; name: string; hex_code: string | null };
        size?: { id: number; label: string };
        stock_quantity: number;
    } | null;
    product: {
        id: number;
        name: string;
        slug: string;
        thumbnail: string | null;
    } | null;
};

export type Cart = {
    id: number;
    status: string;
    items: CartItem[];
    items_count: number;
    subtotal: number;
};

const money = (n: number) =>
    n.toLocaleString(undefined, { style: 'currency', currency: 'USD' });

// Figma shows a $5.00 shipping line (node 31:184), but Order::recalcTotals()
// sets total = subtotal — "no shipping/tax in this project". Charging $5 here
// would quote the customer a total the order never bills, so the row keeps the
// design's shape and states the truth: shipping is free.
export const SHIPPING_FLAT = 0;

function variantLabel(item: CartItem): string {
    const parts: string[] = [];

    if (item.variant?.color) {
        parts.push(`Color: ${item.variant.color.name}`);
    }

    if (item.variant?.size) {
        parts.push(`Size: ${item.variant.size.label}`);
    }

    return parts.join(' · ');
}

export default function CartPage({ cart }: { cart: Cart }) {
    const empty = cart.items.length === 0;
    const total = cart.subtotal + (empty ? 0 : SHIPPING_FLAT);

    function setQuantity(item: CartItem, quantity: number) {
        if (quantity < 1) {
            return;
        }

        router.patch(
            items.update(item.id).url,
            { quantity },
            { preserveScroll: true },
        );
    }

    return (
        <div className="flex min-h-screen flex-col bg-mist font-display text-ink">
            <Head title="Shopping Cart — Chamber" />
            <SiteNavbar variant="dark" cartCount={cart.items_count} />

            <main className="mx-auto w-full max-w-shell flex-1 px-6 pt-12 pb-16 lg:px-16">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Shopping Cart</h1>
                    {!empty && (
                        <button
                            onClick={() =>
                                router.delete(clear().url, {
                                    preserveScroll: true,
                                })
                            }
                            className="text-sm text-slate underline hover:text-gold"
                        >
                            Clear cart
                        </button>
                    )}
                </div>

                {empty ? (
                    <div className="mt-6 rounded-lg border border-line p-12 text-center">
                        <p className="text-slate">Your cart is empty.</p>
                        <Link
                            href="/men"
                            className="mt-4 inline-flex rounded-[6px] bg-gold px-6 py-3 text-[15px] font-medium text-ink transition-opacity hover:opacity-90"
                        >
                            Start shopping
                        </Link>
                    </div>
                ) : (
                    <div className="mt-6 flex flex-col gap-8 lg:flex-row">
                        {/* Items list — Figma node 31:126 */}
                        <div className="flex-1 overflow-hidden rounded-lg border border-line">
                            {cart.items.map((item, i) => (
                                <div
                                    key={item.id}
                                    className={`flex items-center gap-4 px-5 py-4 ${i < cart.items.length - 1 ? 'border-b border-line' : ''}`}
                                >
                                    {item.product?.thumbnail ? (
                                        <img
                                            src={item.product.thumbnail}
                                            alt={item.product.name}
                                            className="h-20 w-20 shrink-0 rounded-[6px] object-cover"
                                        />
                                    ) : (
                                        <div
                                            className={`h-20 w-20 shrink-0 rounded-[6px] bg-gradient-to-br ${tileGradient(item.product?.id ?? item.id)}`}
                                        />
                                    )}

                                    <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                                        <Link
                                            href={`/products/${item.product?.slug ?? ''}`}
                                            className="truncate text-[15px] font-medium text-ink hover:text-gold"
                                        >
                                            {item.product?.name ?? 'Product'}
                                        </Link>
                                        <p className="text-[13px] text-slate">
                                            {variantLabel(item)}
                                        </p>
                                        {item.insufficient_stock && (
                                            <p className="text-[13px] text-[#dc2626]">
                                                Only{' '}
                                                {item.variant?.stock_quantity}{' '}
                                                left in stock
                                            </p>
                                        )}
                                    </div>

                                    {/* Quantity stepper — Figma node 31:132 */}
                                    <div className="flex h-8 w-24 shrink-0 items-center justify-between rounded-[6px] border border-line px-3">
                                        <button
                                            onClick={() =>
                                                setQuantity(
                                                    item,
                                                    item.quantity - 1,
                                                )
                                            }
                                            disabled={item.quantity <= 1}
                                            aria-label="Decrease quantity"
                                            className="text-slate hover:text-ink disabled:opacity-40"
                                        >
                                            <Minus className="h-3.5 w-3.5" />
                                        </button>
                                        <span className="text-sm font-medium">
                                            {item.quantity}
                                        </span>
                                        <button
                                            onClick={() =>
                                                setQuantity(
                                                    item,
                                                    item.quantity + 1,
                                                )
                                            }
                                            aria-label="Increase quantity"
                                            className="text-slate hover:text-ink"
                                        >
                                            <Plus className="h-3.5 w-3.5" />
                                        </button>
                                    </div>

                                    <p className="w-24 shrink-0 text-right text-[15px] font-medium">
                                        {money(item.line_total)}
                                    </p>

                                    <button
                                        onClick={() =>
                                            router.delete(
                                                items.destroy(item.id).url,
                                                { preserveScroll: true },
                                            )
                                        }
                                        aria-label={`Remove ${item.product?.name ?? 'item'}`}
                                        className="shrink-0 text-slate hover:text-[#dc2626]"
                                    >
                                        <X className="h-4 w-4" />
                                    </button>
                                </div>
                            ))}
                        </div>

                        {/* Summary — Figma node 31:179 */}
                        <aside className="flex w-full shrink-0 flex-col gap-4 self-start rounded-lg border border-line p-6 lg:w-[448px]">
                            <h2 className="text-lg font-semibold">
                                Order Summary
                            </h2>
                            <div className="flex justify-between text-sm">
                                <span className="text-slate">Subtotal</span>
                                <span>{money(cart.subtotal)}</span>
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
                                    {money(total)}
                                </span>
                            </div>

                            <Link
                                href="/checkout"
                                className="flex h-12 items-center justify-center rounded-[6px] bg-gold text-[15px] font-medium text-ink transition-opacity hover:opacity-90"
                            >
                                Proceed to Checkout
                            </Link>
                        </aside>
                    </div>
                )}
            </main>

            <SiteFooter />
        </div>
    );
}
