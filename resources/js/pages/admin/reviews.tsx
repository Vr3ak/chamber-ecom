import { Link, router } from '@inertiajs/react';
import { Star } from 'lucide-react';
import { Cell, EmptyRow, Panel, Pill, Row, Table } from '@/components/admin/ui';
import AdminLayout from '@/layouts/admin-layout';

type Review = {
    id: number;
    rating: number;
    body: string | null;
    is_hidden: boolean;
    is_verified: boolean;
    author: string | null;
    product: string | null;
    product_slug: string | null;
    created_at: string | null;
};

type Props = {
    visibility: string | null;
    reviews: Review[];
    pagination: {
        current: number;
        last: number;
        total: number;
        from: number | null;
        to: number | null;
    };
};

const FILTERS = [
    ['', 'All'],
    ['visible', 'Visible'],
    ['hidden', 'Hidden'],
] as const;

/** Admin review moderation — Figma node 48:2947. */
export default function AdminReviews({
    visibility,
    reviews,
    pagination,
}: Props) {
    return (
        <AdminLayout title="Reviews">
            <div className="mb-5 flex flex-wrap gap-2">
                {FILTERS.map(([value, label]) => {
                    const active = (visibility ?? '') === value;

                    return (
                        <Link
                            key={value || 'all'}
                            href={`/admin/reviews${value ? `?visibility=${value}` : ''}`}
                            className={`rounded-[6px] border px-3 py-1.5 text-[13px] font-medium transition-colors ${active ? 'border-ink bg-ink text-white' : 'border-line text-ink hover:border-ink'}`}
                        >
                            {label}
                        </Link>
                    );
                })}
            </div>

            <Panel>
                <Table
                    head={[
                        'Product',
                        'Rating',
                        'Review',
                        'Author',
                        'Date',
                        'Status',
                        '',
                    ]}
                >
                    {reviews.length === 0 ? (
                        <EmptyRow colSpan={7} label="No reviews found." />
                    ) : (
                        reviews.map((r) => (
                            <Row key={r.id}>
                                <Cell className="font-medium">
                                    {r.product_slug ? (
                                        <Link
                                            href={`/products/${r.product_slug}`}
                                            className="hover:text-gold"
                                        >
                                            {r.product}
                                        </Link>
                                    ) : (
                                        (r.product ?? '—')
                                    )}
                                </Cell>
                                <Cell>
                                    <span className="inline-flex items-center gap-1">
                                        <Star className="h-3.5 w-3.5 fill-gold text-gold" />
                                        {r.rating}
                                    </span>
                                </Cell>
                                <Cell className="max-w-[280px] text-slate">
                                    <span className="line-clamp-2">
                                        {r.body ?? '—'}
                                    </span>
                                </Cell>
                                <Cell className="text-slate">
                                    <span className="block">
                                        {r.author ?? 'Anonymous'}
                                    </span>
                                    {r.is_verified && (
                                        <span className="block text-xs text-[#1f5b1e]">
                                            Verified purchase
                                        </span>
                                    )}
                                </Cell>
                                <Cell className="text-slate">
                                    {r.created_at ?? '—'}
                                </Cell>
                                <Cell>
                                    <Pill
                                        value={
                                            r.is_hidden ? 'hidden' : 'visible'
                                        }
                                    />
                                </Cell>
                                <Cell>
                                    <div className="flex justify-end">
                                        {r.is_hidden ? (
                                            <button
                                                onClick={() =>
                                                    router.post(
                                                        `/admin/reviews/${r.id}/unhide`,
                                                        {},
                                                        {
                                                            preserveScroll: true,
                                                        },
                                                    )
                                                }
                                                className="font-medium hover:text-gold"
                                            >
                                                Restore
                                            </button>
                                        ) : (
                                            <button
                                                onClick={() =>
                                                    router.post(
                                                        `/admin/reviews/${r.id}/hide`,
                                                        {},
                                                        {
                                                            preserveScroll: true,
                                                        },
                                                    )
                                                }
                                                className="text-[#dc3232] hover:underline"
                                            >
                                                Hide
                                            </button>
                                        )}
                                    </div>
                                </Cell>
                            </Row>
                        ))
                    )}
                </Table>
            </Panel>

            {pagination.last > 1 && (
                <div className="mt-5 flex items-center justify-between text-sm">
                    <p className="text-slate">
                        Showing {pagination.from ?? 0}–{pagination.to ?? 0} of{' '}
                        {pagination.total}
                    </p>
                    <div className="flex gap-4">
                        {pagination.current > 1 && (
                            <Link
                                href={`/admin/reviews?page=${pagination.current - 1}`}
                                className="text-slate hover:text-gold"
                            >
                                ‹ Prev
                            </Link>
                        )}
                        {pagination.current < pagination.last && (
                            <Link
                                href={`/admin/reviews?page=${pagination.current + 1}`}
                                className="text-slate hover:text-gold"
                            >
                                Next ›
                            </Link>
                        )}
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
