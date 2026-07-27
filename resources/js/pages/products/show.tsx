import { Head, Link, router, usePage } from '@inertiajs/react';
import { Heart, Minus, Plus, Star } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import type { ProductSummary } from '@/components/product-card';
import ProductCard, { tileGradient } from '@/components/product-card';
import SiteFooter from '@/components/site-footer';
import SiteNavbar from '@/components/site-navbar';
import { login } from '@/routes';
import cartItems from '@/routes/cart/items';
import wishlistItems from '@/routes/wishlist/items';

type Option = {
    id: number;
    name?: string;
    label?: string;
    hex_code?: string | null;
};

type Product = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    base_price: number;
    is_trending: boolean;
    brand?: { name: string };
    breadcrumbs?: { name: string; slug: string }[][];
    images?: {
        url: string;
        alt: string | null;
        color_id: number | null;
        is_primary: boolean;
    }[];
    options?: { colors: Option[]; sizes: Option[] };
    variants?: {
        id: number;
        color?: { id: number; name: string };
        size?: { id: number; label: string };
        stock_quantity: number;
        in_stock: boolean;
    }[];
    rating: { average: number | null; count: number };
    reviews?: {
        id: number;
        rating: number;
        body: string | null;
        author?: string;
        created_at: string | null;
    }[];
    total_stock?: number;
};

function Stars({ value }: { value: number }) {
    return (
        <span className="inline-flex">
            {[1, 2, 3, 4, 5].map((n) => (
                <Star
                    key={n}
                    className={`h-4 w-4 ${n <= Math.round(value) ? 'fill-gold text-gold' : 'text-[#d4d4d8]'}`}
                />
            ))}
        </span>
    );
}

export default function ProductShow({
    product,
    related,
}: {
    product: Product;
    related: ProductSummary[];
}) {
    const { auth } = usePage<{ auth: { user: unknown } }>().props;
    const colors = product.options?.colors ?? [];
    const sizes = product.options?.sizes ?? [];
    const variants = product.variants ?? [];
    const [color, setColor] = useState<number | null>(colors[0]?.id ?? null);
    const [size, setSize] = useState<number | null>(null);
    const [qty, setQty] = useState(1);
    const images = product.images ?? [];
    const [activeImage, setActiveImage] = useState(0);

    const activeColor = colors.find((c) => c.id === color);

    // The cart stores variants, not products, so the chosen colour + size has
    // to resolve to a real variant before anything can be added.
    const selectedVariant =
        variants.find((v) => v.color?.id === color && v.size?.id === size) ??
        null;

    /** Sizes actually offered in the selected colour, and whether in stock. */
    function sizeAvailability(sizeId: number) {
        const variant = variants.find(
            (v) => v.color?.id === color && v.size?.id === sizeId,
        );

        return {
            exists: variant !== undefined,
            inStock: (variant?.stock_quantity ?? 0) > 0,
        };
    }

    function addToCart() {
        if (!auth.user) {
            router.visit(login().url);

            return;
        }

        if (!size) {
            toast.error('Please choose a size first.');

            return;
        }

        if (!selectedVariant) {
            toast.error('That colour and size combination is unavailable.');

            return;
        }

        router.post(
            cartItems.store().url,
            { product_variant_id: selectedVariant.id, quantity: qty },
            {
                preserveScroll: true,
                onSuccess: () => toast.success('Added to your cart.'),
                onError: (errors) =>
                    toast.error(
                        errors.quantity ?? 'Could not add that to your cart.',
                    ),
            },
        );
    }

    function addToWishlist() {
        if (!auth.user) {
            router.visit(login().url);

            return;
        }

        router.post(
            wishlistItems.store().url,
            { product_id: product.id },
            {
                preserveScroll: true,
                onSuccess: () => toast.success('Saved to your wishlist.'),
            },
        );
    }

    return (
        <div className="min-h-screen bg-mist font-display text-ink">
            <Head title={`${product.name} — Chamber`} />
            <SiteNavbar variant="dark" />

            <div className="mx-auto w-full max-w-shell px-6 pt-8 pb-16 lg:px-16">
                {/* Breadcrumb — Figma node 43:299 */}
                <nav className="flex flex-wrap gap-1.5 text-[13px] text-slate">
                    <Link href="/" className="hover:text-gold">
                        Home
                    </Link>
                    {product.breadcrumbs?.[0]?.map((c) => (
                        <span key={c.slug} className="flex gap-1.5">
                            <span>/</span>
                            <Link
                                href={`/${c.slug}`}
                                className="hover:text-gold"
                            >
                                {c.name}
                            </Link>
                        </span>
                    ))}
                    <span>/</span>
                    <span className="font-medium text-ink">{product.name}</span>
                </nav>

                <div className="mt-12 grid grid-cols-1 gap-10 lg:grid-cols-2 lg:gap-16">
                    {/* Gallery — Figma node 43:306 */}
                    <div className="flex flex-col gap-3">
                        {images.length > 0 ? (
                            <>
                                <div className="aspect-[600/520] overflow-hidden rounded-[10px] bg-white">
                                    <img
                                        src={images[activeImage].url}
                                        alt={
                                            images[activeImage].alt ??
                                            product.name
                                        }
                                        className="h-full w-full object-cover"
                                    />
                                </div>
                                {images.length > 1 && (
                                    <div className="grid grid-cols-4 gap-3">
                                        {images.map((img, i) => (
                                            <button
                                                key={img.url}
                                                type="button"
                                                onClick={() =>
                                                    setActiveImage(i)
                                                }
                                                aria-label={`View image ${i + 1} of ${images.length}`}
                                                className={`aspect-[132/96] overflow-hidden rounded-[6px] ${i === activeImage ? 'ring-2 ring-ink' : 'opacity-70'}`}
                                            >
                                                <img
                                                    src={img.url}
                                                    alt={img.alt ?? ''}
                                                    className="h-full w-full object-cover"
                                                />
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </>
                        ) : (
                            <>
                                <div
                                    className={`flex aspect-[600/520] items-center justify-center rounded-[10px] bg-gradient-to-br ${tileGradient(product.id)}`}
                                >
                                    <span className="text-2xl font-semibold text-white/90 drop-shadow">
                                        {product.name}
                                    </span>
                                </div>
                                <div className="grid grid-cols-4 gap-3">
                                    {[0, 1, 2, 3].map((i) => (
                                        <div
                                            key={i}
                                            className={`aspect-[132/96] rounded-[6px] bg-gradient-to-br opacity-70 ${tileGradient(product.id + i)} ${i === 0 ? 'ring-2 ring-ink' : ''}`}
                                        />
                                    ))}
                                </div>
                            </>
                        )}
                    </div>

                    {/* Info — Figma node 43:313 */}
                    <div>
                        {product.brand && (
                            <p className="text-[13px] text-slate">
                                {product.brand.name}
                            </p>
                        )}
                        <h1 className="mt-3 text-[32px] leading-tight font-bold">
                            {product.name}
                        </h1>

                        <div className="mt-4 flex items-center gap-2 text-[13px] text-slate">
                            <Stars value={product.rating.average ?? 0} />
                            <span>
                                {product.rating.average ?? '—'} (
                                {product.rating.count} reviews)
                            </span>
                        </div>

                        <p className="mt-4 text-[28px] font-bold">
                            ${product.base_price.toFixed(2)}
                        </p>

                        <hr className="my-5 border-line" />

                        {/* Colours */}
                        {colors.length > 0 && (
                            <div className="mb-5 flex flex-col gap-2.5">
                                <p className="text-sm font-medium">
                                    Color:{' '}
                                    <span className="font-normal text-slate">
                                        {activeColor?.name}
                                    </span>
                                </p>
                                <div className="flex gap-2">
                                    {colors.map((c) => (
                                        <button
                                            key={c.id}
                                            aria-label={c.name}
                                            onClick={() => setColor(c.id)}
                                            className={`h-8 w-8 rounded-full border-2 ${color === c.id ? 'border-ink' : 'border-line'}`}
                                            style={{
                                                backgroundColor:
                                                    c.hex_code ?? '#ccc',
                                            }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Sizes */}
                        {sizes.length > 0 && (
                            <div className="mb-5 flex flex-col gap-2.5">
                                <p className="text-sm font-medium">Size:</p>
                                <div className="flex flex-wrap gap-2">
                                    {sizes.map((s) => {
                                        const { exists, inStock } =
                                            sizeAvailability(s.id);
                                        const disabled = !exists || !inStock;

                                        return (
                                            <button
                                                key={s.id}
                                                onClick={() => setSize(s.id)}
                                                disabled={disabled}
                                                title={
                                                    disabled
                                                        ? 'Not available in this colour'
                                                        : undefined
                                                }
                                                className={`rounded-[6px] border px-3.5 py-2 text-sm font-medium ${
                                                    size === s.id
                                                        ? 'border-ink bg-ink text-white'
                                                        : 'border-line text-ink hover:border-ink'
                                                } ${disabled ? 'cursor-not-allowed text-slate line-through opacity-40 hover:border-line' : ''}`}
                                            >
                                                {s.label}
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>
                        )}

                        {/* Quantity + Add to cart — Figma node 43:351 */}
                        <div className="flex items-center gap-3">
                            <div className="flex h-12 w-[100px] items-center justify-between rounded-[6px] border border-line px-3">
                                <button
                                    onClick={() =>
                                        setQty((q) => Math.max(1, q - 1))
                                    }
                                    className="text-slate hover:text-ink"
                                    aria-label="Decrease quantity"
                                >
                                    <Minus className="h-4 w-4" />
                                </button>
                                <span className="text-base font-medium">
                                    {qty}
                                </span>
                                <button
                                    onClick={() => setQty((q) => q + 1)}
                                    className="text-slate hover:text-ink"
                                    aria-label="Increase quantity"
                                >
                                    <Plus className="h-4 w-4" />
                                </button>
                            </div>
                            <button
                                onClick={addToCart}
                                className="h-12 flex-1 rounded-[6px] bg-gold px-6 text-[15px] font-medium text-ink transition-opacity hover:opacity-90"
                            >
                                Add to Cart
                            </button>
                            <button
                                onClick={addToWishlist}
                                aria-label="Add to wishlist"
                                className="flex h-12 w-12 items-center justify-center rounded-[6px] border border-line text-slate transition-colors hover:border-ink hover:text-ink"
                            >
                                <Heart className="h-5 w-5" />
                            </button>
                        </div>

                        {typeof product.total_stock === 'number' && (
                            <p className="mt-3 text-xs text-dim">
                                {product.total_stock} in stock
                            </p>
                        )}

                        {/* Description */}
                        {product.description && (
                            <>
                                <hr className="my-5 border-line" />
                                <div className="flex flex-col gap-2">
                                    <h2 className="text-base font-semibold">
                                        Description
                                    </h2>
                                    <p className="text-sm leading-relaxed text-slate">
                                        {product.description}
                                    </p>
                                </div>
                            </>
                        )}
                    </div>
                </div>

                {/* Reviews */}
                {product.reviews && product.reviews.length > 0 && (
                    <section className="mt-14">
                        <h2 className="mb-4 text-2xl font-semibold">Reviews</h2>
                        <div className="space-y-4">
                            {product.reviews.map((r) => (
                                <div
                                    key={r.id}
                                    className="rounded-lg border border-line p-4"
                                >
                                    <div className="flex items-center gap-2">
                                        <Stars value={r.rating} />
                                        <span className="text-sm font-medium">
                                            {r.author ?? 'Anonymous'}
                                        </span>
                                        {r.created_at && (
                                            <span className="text-xs text-dim">
                                                {r.created_at}
                                            </span>
                                        )}
                                    </div>
                                    {r.body && (
                                        <p className="mt-2 text-sm text-slate">
                                            {r.body}
                                        </p>
                                    )}
                                </div>
                            ))}
                        </div>
                    </section>
                )}

                {/* Related */}
                {related.length > 0 && (
                    <section className="mt-14">
                        <h2 className="mb-6 text-2xl font-semibold">
                            You might also like
                        </h2>
                        <div className="grid grid-cols-2 gap-6 sm:grid-cols-4">
                            {related.map((p) => (
                                <ProductCard key={p.id} product={p} />
                            ))}
                        </div>
                    </section>
                )}
            </div>

            <SiteFooter />
        </div>
    );
}
