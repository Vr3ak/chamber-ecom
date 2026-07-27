import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import CheckoutController from '@/actions/App/Http/Controllers/CheckoutController';
import InputError from '@/components/input-error';
import { tileGradient } from '@/components/product-card';
import SiteFooter from '@/components/site-footer';
import SiteNavbar from '@/components/site-navbar';
import type { Cart } from '@/pages/shop/cart';

type Address = {
    id: number;
    recipient_name: string;
    phone: string;
    street_line: string;
    city: string;
    province: string | null;
    country: string;
    is_default: boolean;
};

type Props = {
    cart: Cart;
    addresses: Address[];
    user: { name: string; email: string };
};

const money = (n: number) =>
    n.toLocaleString(undefined, { style: 'currency', currency: 'USD' });

const field =
    'h-11 w-full rounded-[6px] border border-line bg-transparent px-3 text-sm placeholder:text-slate focus:border-ink focus:outline-none';
const labelCls = 'text-[13px] font-medium text-ink';

export default function Checkout({ cart, addresses, user }: Props) {
    const preset = addresses.find((a) => a.is_default) ?? addresses[0] ?? null;
    const [selected, setSelected] = useState<number | 'new'>(
        preset ? preset.id : 'new',
    );

    const active = addresses.find((a) => a.id === selected) ?? null;

    return (
        <div className="flex min-h-screen flex-col bg-mist font-display text-ink">
            <Head title="Checkout — Chamber" />
            <SiteNavbar variant="dark" cartCount={cart.items_count} />

            <main className="mx-auto w-full max-w-shell flex-1 px-6 pt-12 pb-16 lg:px-16">
                <h1 className="text-2xl font-semibold">Checkout</h1>

                <Form
                    {...CheckoutController.store.form()}
                    className="mt-6 flex flex-col gap-8 lg:flex-row"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="flex flex-1 flex-col gap-6">
                                {/* Shipping — Figma node 32:375 */}
                                <section className="flex flex-col gap-4 rounded-lg border border-line p-6">
                                    <h2 className="text-lg font-semibold">
                                        Shipping Address
                                    </h2>

                                    {addresses.length > 0 && (
                                        <div className="flex flex-col gap-2">
                                            {addresses.map((a) => (
                                                <label
                                                    key={a.id}
                                                    className={`flex cursor-pointer items-start gap-3 rounded-lg border px-4 py-3 ${selected === a.id ? 'border-2 border-ink' : 'border-line'}`}
                                                >
                                                    <input
                                                        type="radio"
                                                        name="saved_address"
                                                        checked={
                                                            selected === a.id
                                                        }
                                                        onChange={() =>
                                                            setSelected(a.id)
                                                        }
                                                        className="mt-1"
                                                    />
                                                    <span className="text-sm">
                                                        <span className="font-medium">
                                                            {a.recipient_name}
                                                        </span>
                                                        {a.is_default && (
                                                            <span className="ml-2 rounded-full bg-line px-2 py-0.5 text-xs">
                                                                Default
                                                            </span>
                                                        )}
                                                        <span className="mt-0.5 block text-slate">
                                                            {a.street_line},{' '}
                                                            {a.city}
                                                            {a.province
                                                                ? `, ${a.province}`
                                                                : ''}
                                                            , {a.country}
                                                        </span>
                                                        <span className="block text-slate">
                                                            {a.phone}
                                                        </span>
                                                    </span>
                                                </label>
                                            ))}
                                            <label
                                                className={`flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 text-sm ${selected === 'new' ? 'border-2 border-ink' : 'border-line'}`}
                                            >
                                                <input
                                                    type="radio"
                                                    name="saved_address"
                                                    checked={selected === 'new'}
                                                    onChange={() =>
                                                        setSelected('new')
                                                    }
                                                />
                                                Use a different address
                                            </label>
                                        </div>
                                    )}

                                    {/* Bound to the selection so the posted
                                        values always match what's shown. */}
                                    <div className="flex flex-col gap-1.5">
                                        <label
                                            className={labelCls}
                                            htmlFor="shipping_name"
                                        >
                                            Full Name
                                        </label>
                                        <input
                                            id="shipping_name"
                                            name="shipping_name"
                                            required
                                            defaultValue={
                                                active?.recipient_name ??
                                                user.name
                                            }
                                            key={`name-${selected}`}
                                            className={field}
                                        />
                                        <InputError
                                            message={errors.shipping_name}
                                        />
                                    </div>

                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div className="flex flex-col gap-1.5">
                                            <label className={labelCls}>
                                                Email Address
                                            </label>
                                            <input
                                                type="email"
                                                defaultValue={user.email}
                                                readOnly
                                                className={`${field} text-slate`}
                                            />
                                        </div>
                                        <div className="flex flex-col gap-1.5">
                                            <label
                                                className={labelCls}
                                                htmlFor="shipping_phone"
                                            >
                                                Phone Number
                                            </label>
                                            <input
                                                id="shipping_phone"
                                                name="shipping_phone"
                                                required
                                                defaultValue={
                                                    active?.phone ?? ''
                                                }
                                                key={`phone-${selected}`}
                                                className={field}
                                            />
                                            <InputError
                                                message={errors.shipping_phone}
                                            />
                                        </div>
                                    </div>

                                    <div className="flex flex-col gap-1.5">
                                        <label
                                            className={labelCls}
                                            htmlFor="street_line"
                                        >
                                            Street Address
                                        </label>
                                        <input
                                            id="street_line"
                                            name="street_line"
                                            required
                                            defaultValue={
                                                active?.street_line ?? ''
                                            }
                                            key={`street-${selected}`}
                                            className={field}
                                        />
                                        <InputError
                                            message={errors.street_line}
                                        />
                                    </div>

                                    <div className="grid gap-4 sm:grid-cols-3">
                                        <div className="flex flex-col gap-1.5">
                                            <label
                                                className={labelCls}
                                                htmlFor="city"
                                            >
                                                City
                                            </label>
                                            <input
                                                id="city"
                                                name="city"
                                                required
                                                defaultValue={
                                                    active?.city ?? ''
                                                }
                                                key={`city-${selected}`}
                                                className={field}
                                            />
                                            <InputError message={errors.city} />
                                        </div>
                                        <div className="flex flex-col gap-1.5">
                                            <label
                                                className={labelCls}
                                                htmlFor="province"
                                            >
                                                State / Province
                                            </label>
                                            <input
                                                id="province"
                                                name="province"
                                                defaultValue={
                                                    active?.province ?? ''
                                                }
                                                key={`prov-${selected}`}
                                                className={field}
                                            />
                                        </div>
                                        <div className="flex flex-col gap-1.5">
                                            <label
                                                className={labelCls}
                                                htmlFor="postal_code"
                                            >
                                                ZIP / Postal Code
                                            </label>
                                            <input
                                                id="postal_code"
                                                name="postal_code"
                                                className={field}
                                            />
                                        </div>
                                    </div>

                                    <div className="flex flex-col gap-1.5">
                                        <label
                                            className={labelCls}
                                            htmlFor="country"
                                        >
                                            Country
                                        </label>
                                        <input
                                            id="country"
                                            name="country"
                                            required
                                            defaultValue={
                                                active?.country ?? 'Cambodia'
                                            }
                                            key={`country-${selected}`}
                                            className={field}
                                        />
                                        <InputError message={errors.country} />
                                    </div>
                                </section>

  
                                <section className="flex flex-col gap-4 rounded-lg border border-line p-6">
                                    <h2 className="text-lg font-semibold">
                                        Payment Method
                                    </h2>
                                    <div className="flex h-14 items-center gap-3 rounded-lg border-2 border-ink px-4">
                                        <input
                                            type="radio"
                                            checked
                                            readOnly
                                            name="payment_method"
                                        />
                                        <span className="text-[15px] font-medium">
                                            ABA · KHQR
                                        </span>
                                        <span className="ml-auto text-[13px] text-slate">
                                            Scan to pay after placing the order
                                        </span>
                                    </div>
                                </section>
                            </div>

                            {/* Summary — Figma node 32:436 */}
                            <aside className="flex w-full shrink-0 flex-col gap-4 self-start rounded-lg border border-line p-6 lg:w-[448px]">
                                <h2 className="text-lg font-semibold">
                                    Order Summary
                                </h2>

                                {cart.items.map((item) => (
                                    <div
                                        key={item.id}
                                        className="flex items-center gap-3"
                                    >
                                        <div
                                            className={`h-10 w-10 shrink-0 rounded-[6px] bg-gradient-to-br ${tileGradient(item.product?.id ?? item.id)}`}
                                        />
                                        <div className="flex min-w-0 flex-1 flex-col">
                                            <span className="truncate text-[13px] font-medium">
                                                {item.product?.name}
                                            </span>
                                            <span className="text-xs text-slate">
                                                Qty: {item.quantity}
                                            </span>
                                        </div>
                                        <span className="text-[13px] font-medium">
                                            {money(item.line_total)}
                                        </span>
                                    </div>
                                ))}

                                <div className="h-px bg-line" />
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
                                        {money(cart.subtotal)}
                                    </span>
                                </div>

                                {errors.cart && (
                                    <InputError message={errors.cart} />
                                )}

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-[6px] bg-gold px-4 py-3 text-sm font-medium text-ink transition-opacity hover:opacity-90 disabled:opacity-50"
                                >
                                    {processing
                                        ? 'Placing order…'
                                        : 'Place Order'}
                                </button>
                            </aside>
                        </>
                    )}
                </Form>
            </main>

            <SiteFooter />
        </div>
    );
}
