import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import LogoutConfirmModal from '@/components/logout-confirm-modal';
import { cn } from '@/lib/utils';
import type { Auth } from '@/types';

// Sidebar from Figma node 43:364 — 240px, #222831, active row filled #393e46
// with the gold label. "Settings" is rendered in the design but has no admin
// settings screen anywhere in the file, so it is omitted rather than dead.
const NAV = [
    ['Dashboard', '/admin'],
    ['Shoes', '/admin/products'],
    ['Orders', '/admin/orders'],
    ['Customers', '/admin/customers'],
    ['Brands', '/admin/brands'],
    ['Categories', '/admin/categories'],
    ['Colors & Sizes', '/admin/attributes'],
    ['Notifications', '/admin/notifications'],
    ['Reviews', '/admin/reviews'],
] as const;

export default function AdminLayout({
    title,
    actions,
    children,
}: {
    title: string;
    actions?: ReactNode;
    children: ReactNode;
}) {
    const { auth, url } = usePage<{ auth: Auth; url: string }>().props;
    const [confirmingLogout, setConfirmingLogout] = useState(false);
    const path = typeof window !== 'undefined' ? window.location.pathname : url;

    return (
        <div className="flex min-h-screen bg-mist font-display text-ink">
            <Head title={`${title} — Chamber Admin`} />

            <aside className="hidden w-[240px] shrink-0 flex-col gap-1 bg-ink px-4 py-6 lg:flex">
                <Link
                    href="/admin"
                    className="mb-4 px-2 text-lg font-bold text-gold"
                >
                    Chamber
                </Link>

                {NAV.map(([label, href]) => {
                    // /admin must match exactly; the rest match their subtree.
                    const active =
                        href === '/admin'
                            ? path === '/admin'
                            : path.startsWith(href);

                    return (
                        <Link
                            key={href}
                            href={href}
                            className={cn(
                                'flex h-10 items-center gap-3 rounded-[6px] px-3 text-sm transition-colors',
                                active
                                    ? 'bg-slate font-semibold text-gold'
                                    : 'font-medium text-fog hover:text-mist',
                            )}
                        >
                            <span
                                className={cn(
                                    'h-2 w-2 rounded-full',
                                    active ? 'bg-gold' : 'bg-fog/50',
                                )}
                            />
                            {label}
                        </Link>
                    );
                })}
            </aside>

            <div className="flex min-w-0 flex-1 flex-col">
                <header className="flex h-[72px] items-center justify-between gap-4 bg-ink px-6 lg:px-8">
                    <h1 className="truncate text-xl font-semibold text-mist">
                        {title}
                    </h1>

                    <div className="flex items-center gap-4">
                        {actions}
                        <span className="hidden text-[13px] text-fog sm:inline">
                            {auth.user?.name}
                        </span>
                        <button
                            type="button"
                            onClick={() => setConfirmingLogout(true)}
                            className="text-[13px] text-[#dc3232] hover:underline"
                        >
                            Log Out
                        </button>
                    </div>
                </header>

                <main className="min-w-0 flex-1 px-6 py-7 lg:px-8">
                    {children}
                </main>
            </div>

            <LogoutConfirmModal
                open={confirmingLogout}
                onClose={() => setConfirmingLogout(false)}
            />
        </div>
    );
}
