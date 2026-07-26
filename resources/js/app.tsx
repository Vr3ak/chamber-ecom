import { createInertiaApp } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            // These all render their own chrome and must not be wrapped:
            // storefront pages ship SiteNavbar/SiteFooter, admin pages ship
            // AdminLayout (sidebar + topbar).
            case name === 'welcome':
            case name === 'track':
            case name.startsWith('shop/'):
            case name.startsWith('products/'):
            case name.startsWith('checkout/'):
            case name.startsWith('orders/'):
            case name.startsWith('admin/'):
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            // Figma frames the account pages with the storefront header and a
            // bordered account nav (node 48:2051), not the admin sidebar.
            case name.startsWith('settings/'):
                return SettingsLayout;
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
