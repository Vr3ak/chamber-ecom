import { Head, Link, router } from '@inertiajs/react';
import { Search as SearchIcon } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import type { ProductSummary } from '@/components/product-card';
import ProductCard from '@/components/product-card';
import SiteFooter from '@/components/site-footer';
import SiteNavbar from '@/components/site-navbar';

type Props = {
    query: string;
    products: ProductSummary[];
    pagination: {
        current: number;
        last: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    sort: string;
};

const SORTS = [
    ['featured', 'Featured'],
    ['price_asc', 'Price: Low to High'],
    ['price_desc', 'Price: High to Low'],
    ['top_rated', 'Top Rated'],
] as const;

export default function SearchResults({
    query,
    products,
    pagination,
    sort,
}: Props) {
    const [term, setTerm] = useState(query);

    function submit(e: FormEvent) {
        e.preventDefault();
        router.get('/search', { q: term.trim() }, { preserveState: true });
    }

    function pageLink(page: number) {
        const params = new URLSearchParams(window.location.search);
        params.set('page', String(page));

        return `/search?${params.toString()}`;
    }

    return (
        <div className="flex min-h-screen flex-col bg-mist font-display text-ink">
            <Head title={query ? `"${query}" — Chamber` : 'Search — Chamber'} />
            <SiteNavbar variant="dark" />

            <main className="mx-auto w-full max-w-shell flex-1 px-6 pt-8 pb-16 lg:px-16">
                <form onSubmit={submit} className="flex gap-2">
                    <div className="relative flex-1">
                        <SearchIcon className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate" />
                        <input
                            value={term}
                            onChange={(e) => setTerm(e.target.value)}
                            placeholder="Search shoes, brands…"
                            autoFocus
                            className="h-11 w-full rounded-[6px] border border-line bg-transparent pr-3 pl-9 text-sm placeholder:text-slate focus:border-ink focus:outline-none"
                        />
                    </div>
                    <button
                        type="submit"
                        className="rounded-[6px] bg-gold px-6 text-[15px] font-medium text-ink transition-opacity hover:opacity-90"
                    >
                        Search
                    </button>
                </form>

                <div className="mt-6 flex flex-wrap items-end justify-between gap-4">
                    <div className="flex flex-col gap-1">
                        <h1 className="text-2xl font-semibold">
                            {query ? `Results for "${query}"` : 'All shoes'}
                        </h1>
                        <p className="text-sm text-slate">
                            {pagination.total}{' '}
                            {pagination.total === 1 ? 'product' : 'products'}
                        </p>
                    </div>

                    <label className="flex items-center gap-2 rounded-[6px] border border-line px-3 py-2 text-[13px] text-ink">
                        Sort by:
                        <select
                            value={sort}
                            onChange={(e) =>
                                router.get(
                                    '/search',
                                    { q: query, sort: e.target.value },
                                    { preserveState: true },
                                )
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
                    <div className="mt-6 rounded-lg border border-line p-12 text-center">
                        <p className="text-slate">
                            {query
                                ? `Nothing matched "${query}".`
                                : 'Type something to search the catalogue.'}
                        </p>
                        <Link
                            href="/men"
                            className="mt-4 inline-flex rounded-[6px] border border-line px-6 py-3 text-[15px] font-medium text-ink transition-colors hover:border-ink"
                        >
                            Browse all shoes
                        </Link>
                    </div>
                ) : (
                    <div className="mt-6 grid grid-cols-2 gap-6 lg:grid-cols-4">
                        {products.map((p) => (
                            <ProductCard
                                key={p.id}
                                product={p}
                                variant="framed"
                            />
                        ))}
                    </div>
                )}

                {pagination.last > 1 && (
                    <div className="mt-8 flex items-center justify-between text-sm">
                        <p className="text-slate">
                            Showing {pagination.from ?? 0}–{pagination.to ?? 0}{' '}
                            of {pagination.total} products
                        </p>
                        <div className="flex items-center gap-4">
                            {pagination.current > 1 && (
                                <Link
                                    href={pageLink(pagination.current - 1)}
                                    preserveScroll
                                    className="text-slate hover:text-gold"
                                >
                                    ‹ Prev
                                </Link>
                            )}
                            {pagination.current < pagination.last && (
                                <Link
                                    href={pageLink(pagination.current + 1)}
                                    preserveScroll
                                    className="text-slate hover:text-gold"
                                >
                                    Next ›
                                </Link>
                            )}
                        </div>
                    </div>
                )}
            </main>

            <SiteFooter />
        </div>
    );
}
