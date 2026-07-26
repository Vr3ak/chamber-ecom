import { Link } from '@inertiajs/react';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

// Chamber auth card (Figma 01 — Authentication): light page, centered white
// card, gold serif wordmark, serif heading + muted subheading.
export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        // Figma stacks the card 80px from the top rather than centering it
        // in the viewport (node 29:142), and the card shares the page's #eee
        // fill — it reads as an outlined panel, not a raised white sheet.
        <div className="font-display flex min-h-svh flex-col items-center bg-mist p-6 py-10 sm:py-20">
            <div
                className="w-full max-w-[480px] rounded-xl border border-line p-6 text-ink sm:p-10 [&_label]:text-[13px] [&_label]:font-medium [&_label]:text-ink [&_input]:h-11 [&_input]:rounded-[6px] [&_input]:border-line [&_input]:text-sm [&_input]:placeholder:text-slate"
            >
                <div className="mb-6 flex flex-col items-center gap-1.5 text-center">
                    <Link href={home()} className="text-xl font-bold text-gold">
                        Chamber
                    </Link>
                    <h1 className="mt-1 text-2xl font-semibold text-ink">
                        {title}
                    </h1>
                    {description && (
                        <p className="text-sm text-slate">{description}</p>
                    )}
                </div>
                {children}
            </div>
        </div>
    );
}
