import { Link } from '@inertiajs/react';

// Chamber footer — the Footer block from Figma (node 29:79): three columns
// (Shop / Help / Newsletter) over a copyright line.
//
// Figma labels a few destinations that have no route yet (New Arrivals,
// Shipping, Returns, Contact Us). Those render as plain text rather than
// links to nowhere; "Track an order" is a real route so it stays a Link.
export default function SiteFooter() {
    return (
        <footer className="bg-ink px-6 py-12 font-display text-mist lg:px-16">
            <div className="mx-auto w-full max-w-shell">
                <div className="flex flex-col gap-10 sm:flex-row sm:gap-20 lg:gap-30">
                    <div className="flex flex-col gap-3 sm:w-[280px]">
                        <p className="text-sm font-semibold">Shop</p>
                        <Link
                            href="/men"
                            className="text-[13px] transition-colors hover:text-gold"
                        >
                            Men
                        </Link>
                        <Link
                            href="/women"
                            className="text-[13px] transition-colors hover:text-gold"
                        >
                            Women
                        </Link>
                        <Link
                            href="/kids"
                            className="text-[13px] transition-colors hover:text-gold"
                        >
                            Kids
                        </Link>
                        <span className="text-[13px] text-fog">
                            New Arrivals
                        </span>
                    </div>

                    <div className="flex flex-col gap-3 sm:w-[280px]">
                        <p className="text-sm font-semibold">Help</p>
                        <Link
                            href="/track"
                            className="text-[13px] transition-colors hover:text-gold"
                        >
                            Track an order
                        </Link>
                        <span className="text-[13px] text-fog">Shipping</span>
                        <span className="text-[13px] text-fog">Returns</span>
                        <span className="text-[13px] text-fog">Contact Us</span>
                    </div>

                    <div className="flex flex-col gap-3 sm:w-[280px]">
                        <p className="text-sm font-semibold">Newsletter</p>
                        <p className="text-[13px] text-fog">
                            Get 10% off your first order
                        </p>
                    </div>
                </div>

                <p className="mt-6 text-xs text-fog">
                    © {new Date().getFullYear()} Chamber. All rights reserved.
                </p>
            </div>
        </footer>
    );
}
