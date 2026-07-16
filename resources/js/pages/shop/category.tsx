import { FormEvent, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import SiteNavbar from '@/components/site-navbar';
import ProductCard, { ProductSummary } from '@/components/product-card';

type Facets = {
    brands: { id: number; name: string; products_count: number }[];
    colors: { id: number; name: string; hex_code: string | null }[];
    sizes: { id: number; label: string }[];
    price: { min: number; max: number };
};

type Active = {
    brand_id: number[];
    color_id: number[];
    size_id: number[];
    min_price: string | null;
    max_price: string | null;
    sort: string;
};

type Props = {
    gender: string;
    title: string;
    products: ProductSummary[];
    pagination: {
        current: number;
        last: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    facets: Facets;
    active: Active;
};

const serif = { fontFamily: '"IBM Plex Serif", serif' } as const;

const SORTS = [
    ['featured', 'Featured'],
    ['price_asc', 'Price: Low to High'],
    ['price_desc', 'Price: High to Low'],
    ['top_rated', 'Top Rated'],
] as const;

export default function Category({
    gender,
    title,
    products,
    pagination,
    facets,
    active,
}: Props) {
    const [minPrice, setMinPrice] = useState(active.min_price ?? '');
    const [maxPrice, setMaxPrice] = useState(active.max_price ?? '');

    // Merge a partial filter change into the current query and navigate.
    function apply(patch: Record<string, unknown>) {
        const params: Record<string, unknown> = {
            brand_id: active.brand_id.join(',') || undefined,
            color_id: active.color_id.join(',') || undefined,
            size_id: active.size_id.join(',') || undefined,
            min_price: active.min_price || undefined,
            max_price: active.max_price || undefined,
            sort: active.sort !== 'featured' ? active.sort : undefined,
            ...patch,
        };
        Object.keys(params).forEach(
            (k) =>
                (params[k] === undefined || params[k] === '') &&
                delete params[k],
        );
        router.get(`/${gender}`, params, {
            preserveScroll: true,
            preserveState: true,
        });
    }

    function toggle(list: number[], id: number): string | undefined {
        const next = list.includes(id)
            ? list.filter((x) => x !== id)
            : [...list, id];
        return next.join(',') || undefined;
    }

    function submitPrice(e: FormEvent) {
        e.preventDefault();
        apply({
            min_price: minPrice || undefined,
            max_price: maxPrice || undefined,
        });
    }

    function pageLink(page: number) {
        const params = new URLSearchParams(window.location.search);
        params.set('page', String(page));
        return `/${gender}?${params.toString()}`;
    }

    return (
        <div className="min-h-screen bg-white text-[#222831]" style={serif}>
            <Head title={`${title} — Chamber`} />
            <SiteNavbar variant="light" />

            <div className="mx-auto w-full max-w-6xl px-6 py-8 lg:px-16">
                {/* Breadcrumb */}
                <nav className="text-sm text-[#808080]">
                    <Link href="/" className="hover:text-[#ffd369]">
                        Home
                    </Link>
                    <span className="mx-2">/</span>
                    <span className="text-[#222831]">{title}</span>
                </nav>

                <div className="mt-4 flex items-end justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">{title}</h1>
                        <p className="mt-1 text-sm text-[#808080]">
                            {pagination.total} products
                        </p>
                    </div>
                    <label className="flex items-center gap-2 text-sm text-[#393e46]">
                        Sort by:
                        <select
                            value={active.sort}
                            onChange={(e) => apply({ sort: e.target.value })}
                            className="rounded-md border border-[#e4e4e7] bg-white px-3 py-1.5 text-[#222831]"
                        >
                            {SORTS.map(([v, label]) => (
                                <option key={v} value={v}>
                                    {label}
                                </option>
                            ))}
                        </select>
                    </label>
                </div>

                <div className="mt-6 flex flex-col gap-8 lg:flex-row">
                    {/* Filters */}
                    <aside className="w-full shrink-0 lg:w-[220px]">
                        <h2 className="mb-4 text-lg font-semibold">Filters</h2>

                        <form onSubmit={submitPrice} className="mb-6">
                            <p className="mb-2 text-sm font-medium">
                                Price Range
                            </p>
                            <div className="flex items-center gap-2">
                                <input
                                    type="number"
                                    inputMode="numeric"
                                    value={minPrice}
                                    onChange={(e) =>
                                        setMinPrice(e.target.value)
                                    }
                                    placeholder={`$${facets.price.min}`}
                                    className="w-full rounded-md border border-[#e4e4e7] px-2 py-1.5 text-sm"
                                />
                                <input
                                    type="number"
                                    inputMode="numeric"
                                    value={maxPrice}
                                    onChange={(e) =>
                                        setMaxPrice(e.target.value)
                                    }
                                    placeholder={`$${facets.price.max}`}
                                    className="w-full rounded-md border border-[#e4e4e7] px-2 py-1.5 text-sm"
                                />
                            </div>
                            <button
                                type="submit"
                                className="mt-2 text-xs font-medium text-[#393e46] underline hover:text-[#ffd369]"
                            >
                                Apply
                            </button>
                        </form>

                        {facets.sizes.length > 0 && (
                            <div className="mb-6">
                                <p className="mb-2 text-sm font-medium">Size</p>
                                <div className="flex flex-wrap gap-2">
                                    {facets.sizes.map((s) => {
                                        const on = active.size_id.includes(
                                            s.id,
                                        );
                                        return (
                                            <button
                                                key={s.id}
                                                onClick={() =>
                                                    apply({
                                                        size_id: toggle(
                                                            active.size_id,
                                                            s.id,
                                                        ),
                                                    })
                                                }
                                                className={`h-9 w-9 rounded-md border text-sm ${on ? 'border-[#222831] bg-[#222831] text-white' : 'border-[#e4e4e7] text-[#222831] hover:border-[#222831]'}`}
                                            >
                                                {s.label}
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>
                        )}

                        {facets.colors.length > 0 && (
                            <div className="mb-6">
                                <p className="mb-2 text-sm font-medium">
                                    Color
                                </p>
                                <ul className="space-y-2">
                                    {facets.colors.map((c) => (
                                        <li key={c.id}>
                                            <label className="flex cursor-pointer items-center gap-2 text-sm">
                                                <input
                                                    type="checkbox"
                                                    checked={active.color_id.includes(
                                                        c.id,
                                                    )}
                                                    onChange={() =>
                                                        apply({
                                                            color_id: toggle(
                                                                active.color_id,
                                                                c.id,
                                                            ),
                                                        })
                                                    }
                                                />
                                                <span
                                                    className="inline-block h-4 w-4 rounded-full border border-black/10"
                                                    style={{
                                                        backgroundColor:
                                                            c.hex_code ??
                                                            '#ccc',
                                                    }}
                                                />
                                                {c.name}
                                            </label>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        {facets.brands.length > 0 && (
                            <div>
                                <p className="mb-2 text-sm font-medium">
                                    Brand
                                </p>
                                <ul className="space-y-2">
                                    {facets.brands.map((b) => (
                                        <li key={b.id}>
                                            <label className="flex cursor-pointer items-center gap-2 text-sm">
                                                <input
                                                    type="checkbox"
                                                    checked={active.brand_id.includes(
                                                        b.id,
                                                    )}
                                                    onChange={() =>
                                                        apply({
                                                            brand_id: toggle(
                                                                active.brand_id,
                                                                b.id,
                                                            ),
                                                        })
                                                    }
                                                />
                                                {b.name}
                                                <span className="text-[#808080]">
                                                    ({b.products_count})
                                                </span>
                                            </label>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}
                    </aside>

                    {/* Grid */}
                    <div className="flex-1">
                        {products.length === 0 ? (
                            <p className="py-16 text-center text-[#808080]">
                                No products match these filters.
                            </p>
                        ) : (
                            <div className="grid grid-cols-2 gap-6 sm:grid-cols-3">
                                {products.map((p) => (
                                    <ProductCard key={p.id} product={p} />
                                ))}
                            </div>
                        )}

                        {pagination.last > 1 && (
                            <div className="mt-10 flex items-center justify-center gap-2 text-sm">
                                {pagination.current > 1 && (
                                    <Link
                                        href={pageLink(pagination.current - 1)}
                                        preserveScroll
                                        className="px-3 py-1.5 text-[#393e46] hover:text-[#ffd369]"
                                    >
                                        ‹ Prev
                                    </Link>
                                )}
                                {Array.from(
                                    { length: pagination.last },
                                    (_, i) => i + 1,
                                ).map((n) => (
                                    <Link
                                        key={n}
                                        href={pageLink(n)}
                                        preserveScroll
                                        className={`rounded-md px-3 py-1.5 ${n === pagination.current ? 'bg-[#222831] text-white' : 'text-[#393e46] hover:text-[#ffd369]'}`}
                                    >
                                        {n}
                                    </Link>
                                ))}
                                {pagination.current < pagination.last && (
                                    <Link
                                        href={pageLink(pagination.current + 1)}
                                        preserveScroll
                                        className="px-3 py-1.5 text-[#393e46] hover:text-[#ffd369]"
                                    >
                                        Next ›
                                    </Link>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
