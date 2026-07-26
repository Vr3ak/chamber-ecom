import { Link, usePage } from '@inertiajs/react';
import { Heart, Search, ShoppingBag, User } from 'lucide-react';
import { dashboard, login } from '@/routes';

// Chamber navbar — the Header component from Figma (node 48:2052): wordmark,
// centered Men/Women/Kids, and four right-side icons.
//
// Search / wishlist / cart have no backend yet, so those three render inert
// (aria-hidden) rather than linking somewhere broken. The account icon is the
// real auth entry point — login when signed out, dashboard when signed in.
export default function SiteNavbar({
    variant = 'dark',
    cartCount = 0,
}: {
    variant?: 'dark' | 'light';
    cartCount?: number;
}) {
    const { auth } = usePage<{ auth: { user: unknown } }>().props;
    const dark = variant === 'dark';

    const bar = dark ? 'bg-ink' : 'bg-white';
    const navText = dark ? 'text-mist' : 'text-ink';
    const icon = dark ? 'text-fog' : 'text-slate';

    return (
        <header
            className={`font-display flex h-[88px] items-center justify-between border-b border-line px-6 lg:px-16 ${bar}`}
        >
            {/* Logo — 1/3 width so the nav stays centered on the header */}
            <div className="flex-1">
                <Link href="/" className="text-xl font-bold text-gold">
                    Chamber
                </Link>
            </div>

            <nav
                className={`hidden items-center gap-12 text-[15px] font-medium md:flex ${navText}`}
            >
                <Link href="/men" className="transition-colors hover:text-gold">
                    Men
                </Link>
                <Link href="/women" className="transition-colors hover:text-gold">
                    Women
                </Link>
                <Link href="/kids" className="transition-colors hover:text-gold">
                    Kids
                </Link>
            </nav>

            <div className={`flex flex-1 items-center justify-end gap-6 ${icon}`}>
                {/* ponytail: inert until search/wishlist/cart features exist */}
                <Search className="h-[18px] w-[18px]" aria-hidden />
                <Link
                    href={auth.user ? dashboard() : login()}
                    aria-label={auth.user ? 'My account' : 'Log in'}
                    className="transition-colors hover:text-gold"
                >
                    <User className="h-[18px] w-[18px]" />
                </Link>
                <Heart className="h-[18px] w-[18px]" aria-hidden />
                <span className="relative" aria-hidden>
                    <ShoppingBag className="h-[22px] w-[22px]" />
                    {cartCount > 0 && (
                        <span className="absolute -top-1.5 -right-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-gold px-1 text-[9px] font-bold text-ink">
                            {cartCount}
                        </span>
                    )}
                </span>
            </div>
        </header>
    );
}
