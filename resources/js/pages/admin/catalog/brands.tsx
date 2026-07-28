import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Cell, EmptyRow, Panel, Pill, Row, Table } from '@/components/admin/ui';
import ConfirmDeleteModal from '@/components/confirm-delete-modal';
import InputError from '@/components/input-error';
import AdminLayout from '@/layouts/admin-layout';

type Brand = {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
    products_count: number;
};

export default function AdminBrands({ brands }: { brands: Brand[] }) {
    const [deleting, setDeleting] = useState<Brand | null>(null);
    const { errors: pageErrors } = usePage().props as {
        errors: Record<string, string>;
    };

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        is_active: true,
    });

    return (
        <AdminLayout title="Brands">
            {pageErrors?.brand && (
                <p className="mb-4 rounded-lg border border-[#dc3232]/30 bg-[#dc3232]/5 px-4 py-3 text-sm text-[#dc3232]">
                    {pageErrors.brand}
                </p>
            )}

            <Panel title="Add a brand">
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        post('/admin/brands', {
                            preserveScroll: true,
                            onSuccess: () => reset(),
                        });
                    }}
                    className="flex flex-wrap items-end gap-4 p-5"
                >
                    <div className="flex min-w-64 flex-1 flex-col gap-1.5">
                        <label className="text-[13px] font-medium">Name</label>
                        <input
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                            className="h-10 w-full rounded-[6px] border border-line bg-transparent px-3 text-sm focus:border-ink focus:outline-none"
                        />
                        <InputError message={errors.name} />
                    </div>

                    <label className="flex h-10 items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={data.is_active}
                            onChange={(e) =>
                                setData('is_active', e.target.checked)
                            }
                        />
                        Active
                    </label>

                    <button
                        type="submit"
                        disabled={processing}
                        className="h-10 rounded-[6px] bg-gold px-4 text-sm font-medium text-ink transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        Add brand
                    </button>
                </form>
            </Panel>

            <div className="mt-5">
                <Panel title={`${brands.length} brands`}>
                    <Table head={['Name', 'Slug', 'Products', 'Status', '']}>
                        {brands.length === 0 ? (
                            <EmptyRow colSpan={5} label="No brands yet." />
                        ) : (
                            brands.map((b) => (
                                <Row key={b.id}>
                                    <Cell className="font-medium">
                                        {b.name}
                                    </Cell>
                                    <Cell className="text-slate">{b.slug}</Cell>
                                    <Cell className="text-slate">
                                        {b.products_count}
                                    </Cell>
                                    <Cell>
                                        <Pill
                                            value={
                                                b.is_active
                                                    ? 'active'
                                                    : 'hidden'
                                            }
                                        />
                                    </Cell>
                                    <Cell>
                                        <div className="flex justify-end">
                                            <button
                                                onClick={() => setDeleting(b)}
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
            </div>

            <ConfirmDeleteModal
                open={deleting !== null}
                title="Delete this brand?"
                description={`"${deleting?.name}" will be removed. Brands with products cannot be deleted.`}
                deleteUrl={deleting ? `/admin/brands/${deleting.id}` : null}
                onClose={() => setDeleting(null)}
            />
        </AdminLayout>
    );
}
