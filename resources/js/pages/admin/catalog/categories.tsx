import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Cell, EmptyRow, Panel, Row, Table } from '@/components/admin/ui';
import ConfirmDeleteModal from '@/components/confirm-delete-modal';
import InputError from '@/components/input-error';
import AdminLayout from '@/layouts/admin-layout';

type Category = {
    id: number;
    name: string;
    slug: string;
    parent: string | null;
    parent_id: number | null;
    products_count: number;
};

type Props = {
    categories: Category[];
    parents: { id: number; name: string }[];
};

export default function AdminCategories({ categories, parents }: Props) {
    const [deleting, setDeleting] = useState<Category | null>(null);
    const { errors: pageErrors } = usePage().props as {
        errors: Record<string, string>;
    };

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        parent_id: '',
    });

    return (
        <AdminLayout title="Categories">
            {pageErrors?.category && (
                <p className="mb-4 rounded-lg border border-[#dc3232]/30 bg-[#dc3232]/5 px-4 py-3 text-sm text-[#dc3232]">
                    {pageErrors.category}
                </p>
            )}

            <Panel title="Add a category">
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        post('/admin/categories', {
                            preserveScroll: true,
                            onSuccess: () => reset(),
                        });
                    }}
                    className="flex flex-wrap items-end gap-4 p-5"
                >
                    <div className="flex min-w-56 flex-1 flex-col gap-1.5">
                        <label className="text-[13px] font-medium">Name</label>
                        <input
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                            className="h-10 w-full rounded-[6px] border border-line bg-transparent px-3 text-sm focus:border-ink focus:outline-none"
                        />
                        <InputError message={errors.name} />
                    </div>

                    <div className="flex min-w-56 flex-1 flex-col gap-1.5">
                        <label className="text-[13px] font-medium">
                            Parent (optional)
                        </label>
                        <select
                            value={data.parent_id}
                            onChange={(e) =>
                                setData('parent_id', e.target.value)
                            }
                            className="h-10 w-full rounded-[6px] border border-line bg-transparent px-3 text-sm focus:border-ink focus:outline-none"
                        >
                            <option value="">Top level</option>
                            {parents.map((p) => (
                                <option key={p.id} value={p.id}>
                                    {p.name}
                                </option>
                            ))}
                        </select>
                        <InputError message={errors.parent_id} />
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="h-10 rounded-[6px] bg-gold px-4 text-sm font-medium text-ink transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        Add category
                    </button>
                </form>
            </Panel>

            <div className="mt-5">
                <Panel title={`${categories.length} categories`}>
                    <Table head={['Name', 'Slug', 'Parent', 'Products', '']}>
                        {categories.length === 0 ? (
                            <EmptyRow colSpan={5} label="No categories yet." />
                        ) : (
                            categories.map((c) => (
                                <Row key={c.id}>
                                    <Cell className="font-medium">
                                        {c.name}
                                    </Cell>
                                    <Cell className="text-slate">{c.slug}</Cell>
                                    <Cell className="text-slate">
                                        {c.parent ?? '—'}
                                    </Cell>
                                    <Cell className="text-slate">
                                        {c.products_count}
                                    </Cell>
                                    <Cell>
                                        <div className="flex justify-end">
                                            <button
                                                onClick={() => setDeleting(c)}
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
                title="Delete this category?"
                description={`"${deleting?.name}" will be removed. Categories with products or sub-categories cannot be deleted.`}
                deleteUrl={deleting ? `/admin/categories/${deleting.id}` : null}
                onClose={() => setDeleting(null)}
            />
        </AdminLayout>
    );
}
