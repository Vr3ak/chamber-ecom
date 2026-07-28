import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, Star } from 'lucide-react';
import type { ProductSummary } from '@/components/product-card';
import ProductCard from '@/components/product-card';
import SiteFooter from '@/components/site-footer';
import SiteNavbar from '@/components/site-navbar';

const CATEGORIES = [
    ['Men', 'men', 'from-slate-500 to-slate-700'],
    ['Women', 'women', 'from-rose-400 to-pink-600'],
    ['Kids', 'kids', 'from-amber-400 to-orange-600'],
] as const;

const STATS = [
    ['2,000+', 'Products'],
    ['50+', 'Brands'],
    ['Free', 'Shipping $50+'],
] as const;

export default function Welcome() {
    const { trending } = usePage<{ trending: ProductSummary[] }>().props;

    return (
        <>
            <Head title="Chamber — Sneakers & Running Shoes" />
            <div className="min-h-screen bg-mist font-display text-ink">
                <SiteNavbar variant="dark" />

                <section className="flex flex-col bg-ink lg:h-[560px] lg:flex-row">
                    <div className="flex flex-col justify-center gap-5 px-6 py-14 lg:w-[640px] lg:shrink-0 lg:pr-16 lg:pl-24">
                        <span className="w-fit rounded-full border border-gold bg-gold/15 px-3.5 py-1.5 text-xs font-medium text-gold">
                            NEW ARRIVALS — 2026
                        </span>
                        <h1 className="text-5xl leading-tight font-bold lg:text-[52px]">
                            <span className="block text-mist">Step Into</span>
                            <span className="block text-gold">Your Stride</span>
                        </h1>
                        <p className="max-w-[440px] text-base text-fog">
                            Explore handpicked styles for Men, Women &amp; Kids.
                            <br />
                            Free shipping on orders over $50.
                        </p>
                        <div className="flex flex-wrap items-center gap-4">
                            <a
                                href="#trending"
                                className="inline-flex items-center gap-2 rounded-[6px] bg-gold px-7 py-3.5 text-[15px] font-semibold text-ink transition-opacity hover:opacity-90"
                            >
                                Shop Now
                                <ArrowRight className="h-4 w-4" />
                            </a>
                            <Link
                                href="/men"
                                className="inline-flex items-center gap-2 rounded-[6px] border border-[#666] px-7 py-3.5 text-[15px] font-medium text-fog transition-colors hover:border-gold hover:text-gold"
                            >
                                Browse Collection
                            </Link>
                        </div>
                        <dl className="flex gap-10 pt-5">
                            {STATS.map(([value, label]) => (
                                <div
                                    key={label}
                                    className="flex flex-col gap-0.5"
                                >
                                    <dt className="text-xl font-bold text-gold">
                                        {value}
                                    </dt>
                                    <dd className="text-xs text-dim">
                                        {label}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    </div>

                    <div className="relative min-h-[360px] flex-1 overflow-hidden bg-slate lg:min-h-0">
                        <div className="absolute -top-20 left-[350px] h-[500px] w-[500px] rounded-full bg-gold/5" />
                        <div className="absolute top-[380px] -left-20 h-[200px] w-[200px] rounded-full bg-gold/5" />
                        <div className="absolute inset-10 flex items-center justify-center rounded-xl bg-gradient-to-br from-purple-500/40 to-indigo-600/40">
                            <span className="text-lg font-medium text-white/70">
                                Air Glide Runner
                            </span>
                        </div>
                        <div className="absolute bottom-14 left-[55px] flex flex-col gap-1 rounded-[10px] bg-white px-4 py-3 shadow-lg">
                            <p className="text-[11px] font-semibold text-gold">
                                Best Seller
                            </p>
                            <p className="text-sm font-bold text-ink">
                                Air Glide Runner
                            </p>
                            <p className="text-xs font-medium text-slate">
                                $59.00
                            </p>
                        </div>
                        <div className="absolute right-6 bottom-6 inline-flex items-center gap-1.5 rounded-[20px] bg-white px-3 py-2 shadow-lg">
                            <Star className="h-3.5 w-3.5 fill-gold text-gold" />
                            <span className="text-xs font-medium text-ink">
                                4.8 (2.3k reviews)
                            </span>
                        </div>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-shell px-6 py-12 lg:px-16">
                    <h2 className="mb-6 text-2xl font-semibold">
                        Shop by Category
                    </h2>
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        {CATEGORIES.map(([label, slug, gradient]) => (
                            <Link
                                key={slug}
                                href={`/${slug}`}
                                className="group flex flex-col overflow-hidden rounded-lg border border-line bg-mist"
                            >
                                <div
                                    className={`aspect-[400/240] bg-gradient-to-br ${gradient} transition-transform duration-300 group-hover:scale-105`}
                                />
                                <div className="flex h-20 items-center px-4">
                                    <span className="text-base font-medium text-ink transition-colors group-hover:text-gold">
                                        {label}'s Shoes
                                    </span>
                                </div>
                            </Link>
                        ))}
                    </div>
                </section>

                <section
                    id="trending"
                    className="mx-auto w-full max-w-shell px-6 pt-12 pb-16 lg:px-16"
                >
                    <h2 className="mb-6 text-2xl font-semibold">
                        Trending Products
                    </h2>

                    {trending.length === 0 ? (
                        <p className="text-slate">No trending shoes yet.</p>
                    ) : (
                        <div className="grid grid-cols-2 gap-6 lg:grid-cols-4">
                            {trending.map((p) => (
                                <ProductCard key={p.id} product={p} />
                            ))}
                        </div>
                    )}
                </section>

                <SiteFooter />
            </div>
        </>
    );
}
