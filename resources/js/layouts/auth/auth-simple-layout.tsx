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
        <div
            className="flex min-h-svh flex-col items-center justify-center bg-[#f4f4f5] p-6"
            style={{ fontFamily: '"IBM Plex Serif", serif' }}
        >
            <div className="w-full max-w-md rounded-xl border border-black/10 bg-white p-8 text-[#222831] shadow-sm [&_label]:font-medium [&_label]:text-[#222831] sm:p-10">
                <div className="mb-6 flex flex-col items-center gap-1 text-center">
                    <Link
                        href={home()}
                        className="text-xl font-bold text-[#ffd369]"
                    >
                        Chamber
                    </Link>
                    <h1 className="mt-2 text-2xl font-bold text-[#222831]">
                        {title}
                    </h1>
                    {description && (
                        <p className="text-sm text-[#808080]">{description}</p>
                    )}
                </div>
                {children}
            </div>
        </div>
    );
}
