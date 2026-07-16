import { Link, usePage } from '@inertiajs/react';
import { PackageSearch } from 'lucide-react';
import { dashboard, login, register } from '@/routes';

// Chamber navbar (Figma). Dark on the homepage/PDP, light on listing pages.
// Cart/search/wishlist icons are intentionally omitted — those features
// aren't built yet.
export default function SiteNavbar({
    variant = 'dark',
}: {
    variant?: 'dark' | 'light';
}) {
    const { auth } = usePage<{ auth: { user: unknown } }>().props;
    const dark = variant === 'dark';

    const bar = dark ? 'bg-[#222831]' : 'border-b border-[#e4e4e7] bg-white';
    const navText = dark ? 'text-[#eee]' : 'text-[#222831]';
    const muted = dark ? 'text-[#b3b3b3]' : 'text-[#393e46]';

    return (
        <header
            className={`flex h-[88px] items-center justify-between px-6 lg:px-16 ${bar}`}
            style={{ fontFamily: '"IBM Plex Serif", serif' }}
        >
            <Link href="/" className="text-xl font-bold text-[#ffd369]">
                Chamber
            </Link>

            <nav
                className={`hidden items-center gap-12 text-[15px] font-medium md:flex ${navText}`}
            >
                <Link href="/men" className="hover:text-[#ffd369]">
                    Men
                </Link>
                <Link href="/women" className="hover:text-[#ffd369]">
                    Women
                </Link>
                <Link href="/kids" className="hover:text-[#ffd369]">
                    Kids
                </Link>
            </nav>

            <div className="flex items-center gap-5 text-[15px]">
                <Link
                    href="/track"
                    className={`inline-flex items-center gap-1.5 font-medium transition-colors hover:text-[#ffd369] ${muted}`}
                >
                    <PackageSearch className="h-4 w-4" />
                    <span className="hidden sm:inline">Track</span>
                </Link>
                {auth.user ? (
                    <Link
                        href={dashboard()}
                        className="rounded-md bg-[#ffd369] px-5 py-2 font-semibold text-[#222831] transition-opacity hover:opacity-90"
                    >
                        Dashboard
                    </Link>
                ) : (
                    <>
                        <Link
                            href={login()}
                            className={`hidden font-medium transition-colors hover:text-[#ffd369] sm:inline ${navText}`}
                        >
                            Log in
                        </Link>
                        <Link
                            href={register()}
                            className="rounded-md bg-[#ffd369] px-5 py-2 font-semibold text-[#222831] transition-opacity hover:opacity-90"
                        >
                            Register
                        </Link>
                    </>
                )}
            </div>
        </header>
    );
}
