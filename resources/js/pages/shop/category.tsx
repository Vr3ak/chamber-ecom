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
        <div className="font-display min-h-screen bg-mist text-ink">
            <Head title={`${title} — Chamber`} />
            <SiteNavbar variant="dark" />

            <div className="mx-auto w-full max-w-shell px-6 pt-8 pb-16 lg:px-16">
                {/* Breadcrumb — Figma node 32:784 */}
                <nav className="flex gap-1.5 text-[13px] text-slate">
                    <Link href="/" className="hover:text-gold">
                        Home
                    </Link>
                    <span>/</span>
                    <span className="font-medium text-ink">{title}</span>
                </nav>

                <div className="mt-5 flex flex-col gap-1">
                    <h1 className="text-2xl font-semibold">{title}</h1>
                    <p className="text-sm text-slate">
                        {pagination.total} products
                    </p>
                </div>

                <div className="mt-5 flex flex-col gap-8 lg:flex-row">
                    {/* Filters — Figma bounds the sidebar as a 280px card. */}
                    <aside className="flex w-full shrink-0 flex-col gap-6 rounded-lg border border-line p-6 lg:w-[280px]">
                        <h2 className="text-base font-semibold">Filters</h2>

                        <form onSubmit={submitPrice}>
                            <p className="mb-3 text-sm font-semibold">
                                Price Range
                            </p>
                            <div className="flex items-center gap-3">
                                <input
                                    type="number"
                                    inputMode="numeric"
                                    value={minPrice}
                                    onChange={(e) =>
                                        setMinPrice(e.target.value)
                                    }
                                    placeholder={`$${facets.price.min}`}
                                    className="h-9 w-full rounded-[6px] border border-line px-2.5 text-[13px] placeholder:text-slate"
                                />
                                <input
                                    type="number"
                                    inputMode="numeric"
                                    value={maxPrice}
                                    onChange={(e) =>
                                        setMaxPrice(e.target.value)
                                    }
                                    placeholder={`$${facets.price.max}`}
                                    className="h-9 w-full rounded-[6px] border border-line px-2.5 text-[13px] placeholder:text-slate"
                                />
                            </div>
                            <button
                                type="submit"
                                className="mt-2 text-xs font-medium text-slate underline hover:text-gold"
                            >
                                Apply
                            </button>
                        </form>

                        {facets.sizes.length > 0 && (
                            <div>
                                <p className="mb-3 text-sm font-semibold">
                                    Size
                                </p>
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
                                                className={`rounded-[6px] border px-2.5 py-1.5 text-[13px] font-medium ${on ? 'border-ink bg-ink text-white' : 'border-line text-ink hover:border-ink'}`}
                                            >
                                                {s.label}
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>
                        )}

                        {facets.colors.length > 0 && (
                            <div>
                                <p className="mb-3 text-sm font-semibold">
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
                                <p className="mb-3 text-sm font-semibold">
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
                                                <span className="text-dim">
                                                    ({b.products_count})
                                                </span>
                                            </label>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}
                    </aside>

                    {/* Grid — Figma node 32:844 */}
                    <div className="flex flex-1 flex-col gap-5">
                        {/* Sort sits above the grid, right-aligned (node 32:845). */}
                        <div className="flex justify-end">
                            <label className="flex items-center gap-2 rounded-[6px] border border-line px-3 py-2 text-[13px] text-ink">
                                Sort by:
                                <select
                                    value={active.sort}
                                    onChange={(e) =>
                                        apply({ sort: e.target.value })
                                    }
                                    className="bg-transparent text-ink outline-none"
                                >
                                    {SORTS.map(([v, label]) => (
                                        <option key={v} value={v}>
                                            {label}
                                        </option>
                                    ))}
                                </select>
                            </label>
                        </div>

                        {products.length === 0 ? (
                            <p className="py-16 text-center text-slate">
                                No products match these filters.
                            </p>
                        ) : (
                            <div className="grid grid-cols-2 gap-6 sm:grid-cols-3">
                                {products.map((p) => (
                                    <ProductCard
                                        key={p.id}
                                        product={p}
                                        variant="framed"
                                    />
                                ))}
                            </div>
                        )}

                        {/* Pagination row — count left, pager right (node 32:933). */}
                        {products.length > 0 && (
                            <div className="flex flex-wrap items-center justify-between gap-3 pt-4 text-sm">
                                <p className="text-slate">
                                    Showing {pagination.from ?? 0}–
                                    {pagination.to ?? 0} of {pagination.total}{' '}
                                    products
                                </p>
                                {pagination.last > 1 && (
                                    <div className="flex items-center gap-4">
                                        {pagination.current > 1 && (
                                            <Link
                                                href={pageLink(
                                                    pagination.current - 1,
                                                )}
                                                preserveScroll
                                                className="text-slate hover:text-gold"
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
                                                className={
                                                    n === pagination.current
                                                        ? 'font-semibold text-ink'
                                                        : 'text-slate hover:text-gold'
                                                }
                                            >
                                                {n}
                                            </Link>
                                        ))}
                                        {pagination.current <
                                            pagination.last && (
                                            <Link
                                                href={pageLink(
                                                    pagination.current + 1,
                                                )}
                                                preserveScroll
                                                className="text-slate hover:text-gold"
                                            >
                                                Next ›
                                            </Link>
                                        )}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
