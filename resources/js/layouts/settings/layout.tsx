import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import type { PropsWithChildren } from 'react';
import LogoutConfirmModal from '@/components/logout-confirm-modal';
import SiteFooter from '@/components/site-footer';
import SiteNavbar from '@/components/site-navbar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

// Figma's account nav (node 48:2075) also lists Addresses, Order History and
// Notifications. Those have no Inertia page yet — only JSON endpoints or
// nothing at all — so they're omitted rather than linked somewhere broken.
const sidebarNavItems: NavItem[] = [
    { title: 'Profile', href: edit(), icon: null },
    { title: 'Security', href: editSecurity(), icon: null },
    { title: 'Appearance', href: editAppearance(), icon: null },
];

// Chamber account shell — the "My Account" layout from Figma (node 48:2051):
// storefront header, a bordered 240px nav rail, and card-framed content.
export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const [confirmingLogout, setConfirmingLogout] = useState(false);

    return (
        <div className="flex min-h-screen flex-col bg-mist font-display text-ink">
            <Head title="My Account" />
            <SiteNavbar variant="dark" />

            <main className="mx-auto w-full max-w-shell flex-1 px-6 pt-10 pb-16 lg:px-16">
                <h1 className="text-2xl font-semibold">My Account</h1>

                <div className="mt-6 flex flex-col gap-8 lg:flex-row">
                    <aside className="w-full shrink-0 lg:w-[240px]">
                        <nav
                            className="flex flex-col gap-1 rounded-lg border border-line p-2"
                            aria-label="Account"
                        >
                            {sidebarNavItems.map((item, index) => (
                                <Link
                                    key={`${toUrl(item.href)}-${index}`}
                                    href={item.href}
                                    className={cn(
                                        'flex h-11 items-center rounded-[6px] px-4 text-sm transition-colors',
                                        isCurrentOrParentUrl(item.href)
                                            ? 'bg-line/60 font-medium text-ink'
                                            : 'text-slate hover:text-ink',
                                    )}
                                >
                                    {item.icon && (
                                        <item.icon className="mr-2 h-4 w-4" />
                                    )}
                                    {item.title}
                                </Link>
                            ))}

                            <div className="my-1 h-px bg-line" />

                            {/* Figma gates logout behind a confirmation
                                dialog (node 102:4873). */}
                            <button
                                type="button"
                                onClick={() => setConfirmingLogout(true)}
                                className="flex h-11 items-center gap-2.5 rounded-[6px] px-4 text-left text-sm font-medium text-[#dc3232] transition-colors hover:bg-[#dc3232]/10"
                            >
                                <span aria-hidden>→</span>
                                Log Out
                            </button>
                        </nav>
                    </aside>

                    {/* Figma's account fields are 13px labels over 44px
                        inputs (node 48:2090); scope it here so the shared
                        shadcn Input keeps its defaults elsewhere. */}
                    <div className="flex-1 space-y-5 [&_input]:h-11 [&_input]:rounded-[6px] [&_input]:border-line [&_input]:text-sm [&_label]:text-[13px] [&_label]:font-medium [&_label]:text-ink">
                        {children}
                    </div>
                </div>
            </main>

            <LogoutConfirmModal
                open={confirmingLogout}
                onClose={() => setConfirmingLogout(false)}
            />

            <SiteFooter />
        </div>
    );
}
