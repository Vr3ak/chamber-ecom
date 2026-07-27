import { Head, Link, router } from '@inertiajs/react';
import { Heart } from 'lucide-react';
import { tileGradient } from '@/components/product-card';
import SiteFooter from '@/components/site-footer';
import SiteNavbar from '@/components/site-navbar';
import cartItems from '@/routes/cart/items';
import wishlistItems from '@/routes/wishlist/items';

type WishlistItem = {
    id: number;
    created_at: string | null;
    product: {
        id: number;
        name: string;
        slug: string;
        base_price: number;
        is_active: boolean;
        thumbnail: string | null;
    } | null;
};

type Props = {
    wishlist: { id: number; name: string; items: WishlistItem[] };
};

const money = (n: number) =>
    n.toLocaleString(undefined, { style: 'currency', currency: 'USD' });

export default function WishlistPage({ wishlist }: Props) {
    const saved = wishlist.items.filter((i) => i.product);

    return (
        <div className="flex min-h-screen flex-col bg-mist font-display text-ink">
            <Head title="My Wishlist — Chamber" />
            <SiteNavbar variant="dark" />

            <main className="mx-auto w-full max-w-shell flex-1 px-6 pt-12 pb-16 lg:px-16">
                {/* Title row — Figma node 31:21 */}
                <div className="flex flex-col gap-1">
                    <h1 className="text-2xl font-semibold">My Wishlist</h1>
                    <p className="text-sm text-slate">
                        {saved.length} {saved.length === 1 ? 'item' : 'items'}{' '}
                        saved
                    </p>
                </div>

                {saved.length === 0 ? (
                    <div className="mt-6 rounded-lg border border-line p-12 text-center">
                        <p className="text-slate">
                            You haven't saved anything yet.
                        </p>
                        <Link
                            href="/men"
                            className="mt-4 inline-flex rounded-[6px] bg-gold px-6 py-3 text-[15px] font-medium text-ink transition-opacity hover:opacity-90"
                        >
                            Browse shoes
                        </Link>
                    </div>
                ) : (
                    <div className="mt-6 grid grid-cols-2 gap-6 lg:grid-cols-4">
                        {saved.map((item) => (
                            <div
                                key={item.id}
                                className="flex flex-col overflow-hidden rounded-lg border border-line"
                            >
                                <div className="relative">
                                    <Link
                                        href={`/products/${item.product!.slug}`}
                                        className={`block aspect-[304/260] bg-gradient-to-br ${tileGradient(item.product!.id)}`}
                                    />
                                    {/* Filled heart = remove (Figma node 31:29) */}
                                    <button
                                        onClick={() =>
                                            router.delete(
                                                wishlistItems.destroy(item.id)
                                                    .url,
                                                { preserveScroll: true },
                                            )
                                        }
                                        aria-label={`Remove ${item.product!.name} from wishlist`}
                                        className="absolute top-3 right-3 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-ink transition-colors hover:text-[#dc2626]"
                                    >
                                        <Heart className="h-3.5 w-3.5 fill-current" />
                                    </button>
                                </div>

                                <div className="flex flex-col gap-2 p-3">
                                    <Link
                                        href={`/products/${item.product!.slug}`}
                                        className="truncate text-[15px] font-medium text-ink hover:text-gold"
                                    >
                                        {item.product!.name}
                                    </Link>
                                    <p className="text-sm text-slate">
                                        {money(item.product!.base_price)}
                                    </p>
                                    <button
                                        onClick={() =>
                                            router.post(
                                                cartItems.store().url,
                                                {
                                                    product_id:
                                                        item.product!.id,
                                                    quantity: 1,
                                                },
                                                { preserveScroll: true },
                                            )
                                        }
                                        disabled={!item.product!.is_active}
                                        className="flex h-9 items-center justify-center rounded-[6px] bg-gold text-[13px] font-medium text-ink transition-opacity hover:opacity-90 disabled:opacity-50"
                                    >
                                        {item.product!.is_active
                                            ? 'Add to Cart'
                                            : 'Unavailable'}
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </main>

            <SiteFooter />
        </div>
    );
}
