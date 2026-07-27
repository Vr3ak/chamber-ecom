import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export const money = (n: number) =>
    n.toLocaleString(undefined, { style: 'currency', currency: 'USD' });

/** Stat tile — Figma node 43:387. */
export function StatCard({
    label,
    value,
    hint,
}: {
    label: string;
    value: ReactNode;
    hint?: ReactNode;
}) {
    return (
        <div className="flex flex-col gap-1.5 rounded-lg border border-line p-5">
            <p className="text-[13px] text-slate">{label}</p>
            <p className="text-[28px] leading-tight font-bold text-ink">
                {value}
            </p>
            {hint && <p className="text-xs text-slate">{hint}</p>}
        </div>
    );
}

/** Bordered card with an optional header row, used by every admin table. */
export function Panel({
    title,
    action,
    children,
    className,
}: {
    title?: string;
    action?: ReactNode;
    children: ReactNode;
    className?: string;
}) {
    return (
        <section
            className={cn(
                'overflow-hidden rounded-lg border border-line',
                className,
            )}
        >
            {title && (
                <div className="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
                    <h2 className="text-[15px] font-semibold">{title}</h2>
                    {action}
                </div>
            )}
            {children}
        </section>
    );
}

/** Horizontally scrollable table so wide admin grids never break the page. */
export function Table({
    head,
    children,
}: {
    head: ReactNode[];
    children: ReactNode;
}) {
    return (
        <div className="overflow-x-auto">
            <table className="w-full min-w-[720px] border-collapse text-left">
                <thead>
                    <tr className="border-b border-line">
                        {head.map((h, i) => (
                            <th
                                key={i}
                                className="px-5 py-3 text-xs font-medium text-slate"
                            >
                                {h}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>{children}</tbody>
            </table>
        </div>
    );
}

export function Row({ children }: { children: ReactNode }) {
    return <tr className="border-b border-line last:border-0">{children}</tr>;
}

export function Cell({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <td className={cn('px-5 py-3.5 text-[13px]', className)}>{children}</td>
    );
}

const TONES: Record<string, string> = {
    pending: 'bg-line text-slate',
    processing: 'bg-[#fdf0cf] text-[#8a6a1c]',
    paid: 'bg-[#c3ddc5] text-[#1f5b1e]',
    shipped: 'bg-[#d6e4f7] text-[#1c4e8a]',
    delivered: 'bg-[#c3ddc5] text-[#1f5b1e]',
    cancelled: 'bg-[#f7d6d6] text-[#8a1c1c]',
    failed: 'bg-[#f7d6d6] text-[#8a1c1c]',
    sent: 'bg-[#c3ddc5] text-[#1f5b1e]',
    queued: 'bg-line text-slate',
    active: 'bg-[#c3ddc5] text-[#1f5b1e]',
    suspended: 'bg-[#f7d6d6] text-[#8a1c1c]',
    hidden: 'bg-[#f7d6d6] text-[#8a1c1c]',
    visible: 'bg-[#c3ddc5] text-[#1f5b1e]',
};

/** Status pill — Figma node 43:470. */
export function Pill({ value }: { value: string }) {
    return (
        <span
            className={cn(
                'inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize',
                TONES[value] ?? 'bg-line text-slate',
            )}
        >
            {value}
        </span>
    );
}

export function EmptyRow({
    colSpan,
    label,
}: {
    colSpan: number;
    label: string;
}) {
    return (
        <tr>
            <td
                colSpan={colSpan}
                className="px-5 py-12 text-center text-sm text-slate"
            >
                {label}
            </td>
        </tr>
    );
}
