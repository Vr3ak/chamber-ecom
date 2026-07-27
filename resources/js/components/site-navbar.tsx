import { Link, usePage } from '@inertiajs/react';
import {
    Heart,
    LayoutDashboard,
    Search,
    ShoppingBag,
    User,
} from 'lucide-react';
import { login } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { Auth } from '@/types';

// Chamber navbar — the Header component from Figma (node 48:2052): wordmark,
// centered Men/Women/Kids, and four right-side icons.
//
// The account icon is the auth entry point: login when signed out, the
// "My Account" pages when signed in.
export default function SiteNavbar({
    variant = 'dark',
    cartCount,
}: {
    variant?: 'dark' | 'light';
    /** Overrides the shared count; the cart page passes its own live total. */
    cartCount?: number;
}) {
    const { auth, cartCount: sharedCartCount } = usePage<{
        auth: Auth;
        cartCount: number;
    }>().props;
    const dark = variant === 'dark';
    const badge = cartCount ?? sharedCartCount ?? 0;

    const bar = dark ? 'bg-ink' : 'bg-white';
    const navText = dark ? 'text-mist' : 'text-ink';
    const icon = dark ? 'text-fog' : 'text-slate';

    return (
        <header
            className={`flex h-[88px] items-center justify-between border-b border-line px-6 font-display lg:px-16 ${bar}`}
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
                <Link
                    href="/women"
                    className="transition-colors hover:text-gold"
                >
                    Women
                </Link>
                <Link
                    href="/kids"
                    className="transition-colors hover:text-gold"
                >
                    Kids
                </Link>
            </nav>

            <div
                className={`flex flex-1 items-center justify-end gap-6 ${icon}`}
            >
                <Link
                    href="/search"
                    aria-label="Search"
                    className="transition-colors hover:text-gold"
                >
                    <Search className="h-[18px] w-[18px]" />
                </Link>
                <Link
                    href={auth.user ? editProfile() : login()}
                    aria-label={auth.user ? 'My account' : 'Log in'}
                    className="transition-colors hover:text-gold"
                >
                    <User className="h-[18px] w-[18px]" />
                </Link>
                {/* Cart and wishlist require a session; signed-out visitors are
                    sent to login, which redirects back after authenticating. */}
                <Link
                    href={auth.user ? '/wishlist' : login().url}
                    aria-label="Wishlist"
                    className="transition-colors hover:text-gold"
                >
                    <Heart className="h-[18px] w-[18px]" />
                </Link>
                <Link
                    href={auth.user ? '/cart' : login().url}
                    aria-label="Cart"
                    className="relative transition-colors hover:text-gold"
                >
                    <ShoppingBag className="h-[22px] w-[22px]" />
                    {badge > 0 && (
                        <span className="absolute -top-1.5 -right-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-gold px-1 text-[9px] font-bold text-ink">
                            {badge}
                        </span>
                    )}
                </Link>
                {auth.user?.is_admin && (
                    <Link
                        href="/admin"
                        aria-label="Admin dashboard"
                        className="transition-colors hover:text-gold"
                    >
                        <LayoutDashboard className="h-[18px] w-[18px]" />
                    </Link>
                )}
            </div>
        </header>
    );
}
