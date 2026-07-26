import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import {
    Cell,
    EmptyRow,
    money,
    Panel,
    Pill,
    Row,
    Table,
} from '@/components/admin/ui';
import ConfirmDeleteModal from '@/components/confirm-delete-modal';
import AdminLayout from '@/layouts/admin-layout';

type Product = {
    id: number;
    name: string;
    slug: string;
    brand: string | null;
    base_price: number;
    is_active: boolean;
    variants_count: number;
    stock: number;
};

type Props = {
    query: string;
    products: Product[];
    pagination: {
        current: number;
        last: number;
        total: number;
        from: number | null;
        to: number | null;
    };
};

export default function AdminProducts({ query, products, pagination }: Props) {
    const [term, setTerm] = useState(query);
    const [deleting, setDeleting] = useState<Product | null>(null);

    function search(e: FormEvent) {
        e.preventDefault();
        router.get(
            '/admin/products',
            { q: term.trim() },
            { preserveState: true },
        );
    }

    return (
        <AdminLayout
            title="Shoes"
            actions={
                <Link
                    href="/admin/products/create"
                    className="rounded-[6px] bg-gold px-4 py-2 text-[13px] font-medium text-ink transition-opacity hover:opacity-90"
                >
                    + Add Shoe
                </Link>
            }
        >
            <form onSubmit={search} className="mb-5 flex gap-2">
                <input
                    value={term}
                    onChange={(e) => setTerm(e.target.value)}
                    placeholder="Search by name or brand…"
                    className="h-10 w-full max-w-sm rounded-[6px] border border-line bg-transparent px-3 text-sm placeholder:text-slate focus:border-ink focus:outline-none"
                />
                <button
                    type="submit"
                    className="rounded-[6px] border border-line px-4 text-sm font-medium transition-colors hover:border-ink"
                >
                    Search
                </button>
            </form>

            <Panel>
                <Table
                    head={[
                        'Product',
                        'Brand',
                        'Price',
                        'Variants',
                        'Stock',
                        'Status',
                        '',
                    ]}
                >
                    {products.length === 0 ? (
                        <EmptyRow colSpan={7} label="No products found." />
                    ) : (
                        products.map((p) => (
                            <Row key={p.id}>
                                <Cell className="font-medium">
                                    <Link
                                        href={`/admin/products/${p.id}/edit`}
                                        className="hover:text-gold"
                                    >
                                        {p.name}
                                    </Link>
                                </Cell>
                                <Cell className="text-slate">
                                    {p.brand ?? '—'}
                                </Cell>
                                <Cell>{money(p.base_price)}</Cell>
                                <Cell>
                                    <Link
                                        href={`/admin/products/${p.id}/variants`}
                                        className="text-slate underline hover:text-gold"
                                    >
                                        {p.variants_count}
                                    </Link>
                                </Cell>
                                <Cell
                                    className={
                                        p.stock === 0
                                            ? 'font-medium text-[#dc3232]'
                                            : 'text-slate'
                                    }
                                >
                                    {p.stock}
                                </Cell>
                                <Cell>
                                    <Pill
                                        value={
                                            p.is_active ? 'active' : 'hidden'
                                        }
                                    />
                                </Cell>
                                <Cell>
                                    <div className="flex justify-end gap-3">
                                        <Link
                                            href={`/admin/products/${p.id}/edit`}
                                            className="font-medium hover:text-gold"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            onClick={() => setDeleting(p)}
                                            className="text-[#dc3232] hover:underline"
                                        >
                                            Delete
                                        </button>
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
                                href={`/admin/products?page=${pagination.current - 1}&q=${encodeURIComponent(query)}`}
                                className="text-slate hover:text-gold"
                            >
                                ‹ Prev
                            </Link>
                        )}
                        {pagination.current < pagination.last && (
                            <Link
                                href={`/admin/products?page=${pagination.current + 1}&q=${encodeURIComponent(query)}`}
                                className="text-slate hover:text-gold"
                            >
                                Next ›
                            </Link>
                        )}
                    </div>
                </div>
            )}

            <ConfirmDeleteModal
                open={deleting !== null}
                title="Delete this shoe?"
                description={`"${deleting?.name}" and its variants will be permanently removed. This cannot be undone.`}
                deleteUrl={deleting ? `/admin/products/${deleting.id}` : null}
                onClose={() => setDeleting(null)}
            />
        </AdminLayout>
    );
}
