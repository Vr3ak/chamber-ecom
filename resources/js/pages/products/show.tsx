import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Minus, Plus, Star } from 'lucide-react';
import { toast } from 'sonner';
import SiteNavbar from '@/components/site-navbar';
import ProductCard, {
    ProductSummary,
    tileGradient,
} from '@/components/product-card';

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
    options?: { colors: Option[]; sizes: Option[] };
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

const serif = { fontFamily: '"IBM Plex Serif", serif' } as const;

function Stars({ value }: { value: number }) {
    return (
        <span className="inline-flex">
            {[1, 2, 3, 4, 5].map((n) => (
                <Star
                    key={n}
                    className={`h-4 w-4 ${n <= Math.round(value) ? 'fill-[#ffd369] text-[#ffd369]' : 'text-[#d4d4d8]'}`}
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
    const colors = product.options?.colors ?? [];
    const sizes = product.options?.sizes ?? [];
    const [color, setColor] = useState<number | null>(colors[0]?.id ?? null);
    const [size, setSize] = useState<number | null>(null);
    const [qty, setQty] = useState(1);

    const activeColor = colors.find((c) => c.id === color);

    function addToCart() {
        if (!size) {
            toast.error('Please choose a size first.');
            return;
        }
        // ponytail: cart isn't built yet — this stubs the Figma action.
        toast('Cart isn’t available yet — coming soon.');
    }

    return (
        <div className="min-h-screen bg-white text-[#222831]" style={serif}>
            <Head title={`${product.name} — Chamber`} />
            <SiteNavbar variant="dark" />

            <div className="mx-auto w-full max-w-6xl px-6 py-8 lg:px-16">
                {/* Breadcrumb */}
                <nav className="text-sm text-[#808080]">
                    <Link href="/" className="hover:text-[#ffd369]">
                        Home
                    </Link>
                    {product.breadcrumbs?.[0]?.map((c) => (
                        <span key={c.slug}>
                            <span className="mx-2">/</span>
                            <Link
                                href={`/${c.slug}`}
                                className="hover:text-[#ffd369]"
                            >
                                {c.name}
                            </Link>
                        </span>
                    ))}
                    <span className="mx-2">/</span>
                    <span className="text-[#222831]">{product.name}</span>
                </nav>

                <div className="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-2">
                    {/* Gallery */}
                    <div>
                        <div
                            className={`flex aspect-square items-center justify-center rounded-xl bg-gradient-to-br ${tileGradient(product.id)}`}
                        >
                            <span className="text-2xl font-semibold text-white/90 drop-shadow">
                                {product.name}
                            </span>
                        </div>
                        <div className="mt-3 grid grid-cols-4 gap-3">
                            {[0, 1, 2, 3].map((i) => (
                                <div
                                    key={i}
                                    className={`aspect-square rounded-md bg-gradient-to-br opacity-70 ${tileGradient(product.id + i)}`}
                                />
                            ))}
                        </div>
                    </div>

                    {/* Info */}
                    <div>
                        {product.brand && (
                            <p className="text-sm text-[#808080]">
                                {product.brand.name}
                            </p>
                        )}
                        <h1 className="mt-1 text-4xl font-bold">
                            {product.name}
                        </h1>

                        <div className="mt-3 flex items-center gap-2 text-sm text-[#393e46]">
                            <Stars value={product.rating.average ?? 0} />
                            <span>
                                {product.rating.average ?? '—'} (
                                {product.rating.count} reviews)
                            </span>
                        </div>

                        <p className="mt-4 text-2xl font-bold">
                            ${product.base_price.toFixed(2)}
                        </p>

                        <hr className="my-6 border-[#e4e4e7]" />

                        {/* Colours */}
                        {colors.length > 0 && (
                            <div className="mb-5">
                                <p className="mb-2 text-sm font-medium">
                                    Color:{' '}
                                    <span className="text-[#808080]">
                                        {activeColor?.name}
                                    </span>
                                </p>
                                <div className="flex gap-2">
                                    {colors.map((c) => (
                                        <button
                                            key={c.id}
                                            aria-label={c.name}
                                            onClick={() => setColor(c.id)}
                                            className={`h-8 w-8 rounded-full border-2 ${color === c.id ? 'border-[#222831]' : 'border-[#e4e4e7]'}`}
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
                            <div className="mb-6">
                                <p className="mb-2 text-sm font-medium">
                                    Size:
                                </p>
                                <div className="flex flex-wrap gap-2">
                                    {sizes.map((s) => (
                                        <button
                                            key={s.id}
                                            onClick={() => setSize(s.id)}
                                            className={`min-w-[48px] rounded-md border px-3 py-2 text-sm ${size === s.id ? 'border-[#222831] bg-[#222831] text-white' : 'border-[#e4e4e7] text-[#222831] hover:border-[#222831]'}`}
                                        >
                                            {s.label}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Quantity + Add to cart */}
                        <div className="flex items-center gap-3">
                            <div className="flex items-center rounded-md border border-[#e4e4e7]">
                                <button
                                    onClick={() =>
                                        setQty((q) => Math.max(1, q - 1))
                                    }
                                    className="px-3 py-2 text-[#393e46] hover:text-[#222831]"
                                    aria-label="Decrease quantity"
                                >
                                    <Minus className="h-4 w-4" />
                                </button>
                                <span className="w-8 text-center text-sm">
                                    {qty}
                                </span>
                                <button
                                    onClick={() => setQty((q) => q + 1)}
                                    className="px-3 py-2 text-[#393e46] hover:text-[#222831]"
                                    aria-label="Increase quantity"
                                >
                                    <Plus className="h-4 w-4" />
                                </button>
                            </div>
                            <button
                                onClick={addToCart}
                                className="flex-1 rounded-md bg-[#ffd369] px-6 py-3 font-semibold text-[#222831] transition-opacity hover:opacity-90"
                            >
                                Add to Cart
                            </button>
                        </div>

                        {typeof product.total_stock === 'number' && (
                            <p className="mt-3 text-xs text-[#808080]">
                                {product.total_stock} in stock
                            </p>
                        )}

                        {/* Description */}
                        {product.description && (
                            <div className="mt-8">
                                <h2 className="mb-2 font-semibold">
                                    Description
                                </h2>
                                <p className="text-sm leading-relaxed text-[#393e46]">
                                    {product.description}
                                </p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Reviews */}
                {product.reviews && product.reviews.length > 0 && (
                    <section className="mt-14">
                        <h2 className="mb-4 text-xl font-bold">Reviews</h2>
                        <div className="space-y-4">
                            {product.reviews.map((r) => (
                                <div
                                    key={r.id}
                                    className="rounded-lg border border-[#e4e4e7] p-4"
                                >
                                    <div className="flex items-center gap-2">
                                        <Stars value={r.rating} />
                                        <span className="text-sm font-medium">
                                            {r.author ?? 'Anonymous'}
                                        </span>
                                        {r.created_at && (
                                            <span className="text-xs text-[#808080]">
                                                {r.created_at}
                                            </span>
                                        )}
                                    </div>
                                    {r.body && (
                                        <p className="mt-2 text-sm text-[#393e46]">
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
                        <h2 className="mb-6 text-xl font-bold">
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
        </div>
    );
}
