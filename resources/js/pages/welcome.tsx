import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, Star } from 'lucide-react';
import SiteNavbar from '@/components/site-navbar';
import ProductCard, { ProductSummary } from '@/components/product-card';

const serif = { fontFamily: '"IBM Plex Serif", serif' } as const;

const CATEGORIES = [
    ['Men', 'men', 'from-slate-500 to-slate-700'],
    ['Women', 'women', 'from-rose-400 to-pink-600'],
    ['Kids', 'kids', 'from-amber-400 to-orange-600'],
] as const;

export default function Welcome() {
    const { trending } = usePage<{ trending: ProductSummary[] }>().props;

    return (
        <>
            <Head title="Chamber — Sneakers & Running Shoes" />
            <div
                className="min-h-screen bg-[#f4f4f5] text-[#222831]"
                style={serif}
            >
                <SiteNavbar variant="dark" />

                {/* Hero */}
                <section className="flex flex-col bg-[#222831] lg:flex-row lg:items-stretch">
                    <div className="flex flex-col justify-center gap-5 px-6 py-14 lg:w-[640px] lg:px-24 lg:py-20">
                        <span className="w-fit rounded-full border border-[#ffd369] bg-[#ffd369]/15 px-3.5 py-1.5 text-xs font-medium text-[#ffd369]">
                            NEW ARRIVALS — 2026
                        </span>
                        <h1 className="text-5xl leading-tight font-bold lg:text-[52px]">
                            <span className="block text-[#eee]">Step Into</span>
                            <span className="block text-[#ffd369]">
                                Your Stride
                            </span>
                        </h1>
                        <p className="max-w-md text-base text-[#b3b3b3]">
                            Explore handpicked styles for Men, Women &amp; Kids.
                            <br />
                            Free shipping on orders over $50.
                        </p>
                        <div className="mt-1 flex flex-wrap gap-4">
                            <a
                                href="#trending"
                                className="inline-flex items-center gap-2 rounded-md bg-[#ffd369] px-7 py-3.5 text-[15px] font-semibold text-[#222831] transition-opacity hover:opacity-90"
                            >
                                Shop Now
                                <ArrowRight className="h-4 w-4" />
                            </a>
                            <Link
                                href="/men"
                                className="inline-flex items-center gap-2 rounded-md border border-[#666] px-7 py-3.5 text-[15px] font-medium text-[#b3b3b3] transition-colors hover:border-[#ffd369] hover:text-[#ffd369]"
                            >
                                Browse Collection
                            </Link>
                        </div>
                        <dl className="mt-5 flex gap-10">
                            {[
                                ['2,000+', 'Products'],
                                ['50+', 'Brands'],
                                ['Free', 'Shipping $50+'],
                            ].map(([value, label]) => (
                                <div key={label}>
                                    <dt className="text-xl font-bold text-[#ffd369]">
                                        {value}
                                    </dt>
                                    <dd className="text-xs text-[#808080]">
                                        {label}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    </div>

                    {/* Hero visual — dark panel with deco circles + floating cards.
                        The seed ships no real product photos, so the shoe area
                        is a styled placeholder rather than an <img>. */}
                    <div className="relative min-h-[360px] flex-1 overflow-hidden bg-[#393e46] lg:min-h-[560px]">
                        <div className="absolute -top-20 right-10 h-[500px] w-[500px] rounded-full bg-[#ffd369]/5" />
                        <div className="absolute bottom-16 -left-20 h-[200px] w-[200px] rounded-full bg-[#ffd369]/5" />
                        <div className="absolute inset-10 flex items-center justify-center rounded-xl bg-gradient-to-br from-purple-500/40 to-indigo-600/40">
                            <span className="text-lg font-medium text-white/70">
                                Air Glide Runner
                            </span>
                        </div>
                        <div className="absolute bottom-14 left-6 rounded-[10px] bg-white px-4 py-3 shadow-lg">
                            <p className="text-[11px] font-semibold text-[#ffd369]">
                                Best Seller
                            </p>
                            <p className="text-sm font-bold text-[#222831]">
                                Air Glide Runner
                            </p>
                            <p className="text-xs font-medium text-[#393e46]">
                                $59.00
                            </p>
                        </div>
                        <div className="absolute right-6 bottom-6 inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-2 shadow-lg">
                            <Star className="h-3.5 w-3.5 fill-[#ffd369] text-[#ffd369]" />
                            <span className="text-xs font-medium text-[#222831]">
                                4.8 (2.3k reviews)
                            </span>
                        </div>
                    </div>
                </section>

                {/* Shop by Category */}
                <section className="mx-auto w-full max-w-6xl px-6 py-16 lg:px-16">
                    <h2 className="mb-8 text-2xl font-bold">
                        Shop by Category
                    </h2>
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        {CATEGORIES.map(([label, slug, gradient]) => (
                            <Link
                                key={slug}
                                href={`/${slug}`}
                                className="group relative flex aspect-[4/3] items-end overflow-hidden rounded-xl"
                            >
                                <div
                                    className={`absolute inset-0 bg-gradient-to-br ${gradient} transition-transform duration-300 group-hover:scale-105`}
                                />
                                <div className="relative w-full bg-black/30 px-5 py-4">
                                    <span className="text-lg font-semibold text-white">
                                        {label}'s Shoes
                                    </span>
                                </div>
                            </Link>
                        ))}
                    </div>
                </section>

                {/* Trending */}
                <section
                    id="trending"
                    className="mx-auto w-full max-w-6xl px-6 pb-16 lg:px-16"
                >
                    <h2 className="mb-8 text-2xl font-bold">
                        Trending Products
                    </h2>

                    {trending.length === 0 ? (
                        <p className="text-[#393e46]">No trending shoes yet.</p>
                    ) : (
                        <div className="grid grid-cols-2 gap-6 lg:grid-cols-4">
                            {trending.map((p) => (
                                <ProductCard key={p.id} product={p} />
                            ))}
                        </div>
                    )}
                </section>

                {/* Footer */}
                <footer className="bg-[#222831] py-8 text-[#b3b3b3]">
                    <div className="mx-auto flex w-full max-w-6xl flex-col items-center justify-between gap-3 px-6 text-sm sm:flex-row lg:px-16">
                        <span className="font-bold text-[#ffd369]">
                            Chamber
                        </span>
                        <span>
                            © {new Date().getFullYear()} Chamber. All rights
                            reserved.
                        </span>
                        <Link
                            href="/track"
                            className="transition-colors hover:text-[#ffd369]"
                        >
                            Track an order
                        </Link>
                    </div>
                </footer>
            </div>
        </>
    );
}
