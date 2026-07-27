import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Cell, EmptyRow, Panel, Row, Table } from '@/components/admin/ui';
import ConfirmDeleteModal from '@/components/confirm-delete-modal';
import InputError from '@/components/input-error';
import AdminLayout from '@/layouts/admin-layout';

type Color = {
    id: number;
    name: string;
    hex_code: string | null;
    variants_count: number;
};

type Size = {
    id: number;
    label: string;
    foot_length_cm: number | null;
    sort_order: number | null;
    variants_count: number;
};

type Target = { kind: 'color' | 'size'; id: number; label: string };

const input =
    'h-10 w-full rounded-[6px] border border-line bg-transparent px-3 text-sm focus:border-ink focus:outline-none';

/** Colours & Sizes — Figma node 48:2490 (one screen, two tables). */
export default function AdminAttributes({
    colors,
    sizes,
}: {
    colors: Color[];
    sizes: Size[];
}) {
    const [deleting, setDeleting] = useState<Target | null>(null);
    const { errors: pageErrors } = usePage().props as {
        errors: Record<string, string>;
    };

    const colorForm = useForm({ name: '', hex_code: '#000000' });
    const sizeForm = useForm({
        label: '',
        foot_length_cm: '',
        sort_order: '',
    });

    return (
        <AdminLayout title="Colors & Sizes">
            {(pageErrors?.color || pageErrors?.size) && (
                <p className="mb-4 rounded-lg border border-[#dc3232]/30 bg-[#dc3232]/5 px-4 py-3 text-sm text-[#dc3232]">
                    {pageErrors.color ?? pageErrors.size}
                </p>
            )}

            <div className="grid gap-5 xl:grid-cols-2">
                {/* ---- Colours ---- */}
                <div className="flex flex-col gap-5">
                    <Panel title="Add a colour">
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                colorForm.post('/admin/colors', {
                                    preserveScroll: true,
                                    onSuccess: () => colorForm.reset(),
                                });
                            }}
                            className="flex flex-wrap items-end gap-3 p-5"
                        >
                            <div className="flex min-w-40 flex-1 flex-col gap-1.5">
                                <label className="text-[13px] font-medium">
                                    Name
                                </label>
                                <input
                                    value={colorForm.data.name}
                                    onChange={(e) =>
                                        colorForm.setData(
                                            'name',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    className={input}
                                />
                                <InputError message={colorForm.errors.name} />
                            </div>
                            <div className="flex flex-col gap-1.5">
                                <label className="text-[13px] font-medium">
                                    Hex
                                </label>
                                <input
                                    type="color"
                                    value={colorForm.data.hex_code}
                                    onChange={(e) =>
                                        colorForm.setData(
                                            'hex_code',
                                            e.target.value,
                                        )
                                    }
                                    className="h-10 w-16 rounded-[6px] border border-line bg-transparent"
                                />
                            </div>
                            <button
                                type="submit"
                                disabled={colorForm.processing}
                                className="h-10 rounded-[6px] bg-gold px-4 text-sm font-medium text-ink transition-opacity hover:opacity-90 disabled:opacity-50"
                            >
                                Add
                            </button>
                        </form>
                    </Panel>

                    <Panel title={`${colors.length} colours`}>
                        <Table head={['Colour', 'Hex', 'In use', '']}>
                            {colors.length === 0 ? (
                                <EmptyRow colSpan={4} label="No colours yet." />
                            ) : (
                                colors.map((c) => (
                                    <Row key={c.id}>
                                        <Cell>
                                            <span className="flex items-center gap-2 font-medium">
                                                <span
                                                    className="h-4 w-4 rounded-full border border-line"
                                                    style={{
                                                        backgroundColor:
                                                            c.hex_code ??
                                                            '#ccc',
                                                    }}
                                                />
                                                {c.name}
                                            </span>
                                        </Cell>
                                        <Cell className="text-slate">
                                            {c.hex_code ?? '—'}
                                        </Cell>
                                        <Cell className="text-slate">
                                            {c.variants_count}
                                        </Cell>
                                        <Cell>
                                            <div className="flex justify-end">
                                                <button
                                                    onClick={() =>
                                                        setDeleting({
                                                            kind: 'color',
                                                            id: c.id,
                                                            label: c.name,
                                                        })
                                                    }
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

                {/* ---- Sizes ---- */}
                <div className="flex flex-col gap-5">
                    <Panel title="Add a size">
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                sizeForm.post('/admin/sizes', {
                                    preserveScroll: true,
                                    onSuccess: () => sizeForm.reset(),
                                });
                            }}
                            className="flex flex-wrap items-end gap-3 p-5"
                        >
                            <div className="flex min-w-28 flex-1 flex-col gap-1.5">
                                <label className="text-[13px] font-medium">
                                    Label
                                </label>
                                <input
                                    value={sizeForm.data.label}
                                    onChange={(e) =>
                                        sizeForm.setData(
                                            'label',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    placeholder="9.5"
                                    className={input}
                                />
                                <InputError message={sizeForm.errors.label} />
                            </div>
                            <div className="flex min-w-28 flex-1 flex-col gap-1.5">
                                <label className="text-[13px] font-medium">
                                    Foot length (cm)
                                </label>
                                <input
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    value={sizeForm.data.foot_length_cm}
                                    onChange={(e) =>
                                        sizeForm.setData(
                                            'foot_length_cm',
                                            e.target.value,
                                        )
                                    }
                                    className={input}
                                />
                            </div>
                            <div className="flex w-24 flex-col gap-1.5">
                                <label className="text-[13px] font-medium">
                                    Order
                                </label>
                                <input
                                    type="number"
                                    value={sizeForm.data.sort_order}
                                    onChange={(e) =>
                                        sizeForm.setData(
                                            'sort_order',
                                            e.target.value,
                                        )
                                    }
                                    className={input}
                                />
                            </div>
                            <button
                                type="submit"
                                disabled={sizeForm.processing}
                                className="h-10 rounded-[6px] bg-gold px-4 text-sm font-medium text-ink transition-opacity hover:opacity-90 disabled:opacity-50"
                            >
                                Add
                            </button>
                        </form>
                    </Panel>

                    <Panel title={`${sizes.length} sizes`}>
                        <Table head={['Size', 'Foot length', 'In use', '']}>
                            {sizes.length === 0 ? (
                                <EmptyRow colSpan={4} label="No sizes yet." />
                            ) : (
                                sizes.map((s) => (
                                    <Row key={s.id}>
                                        <Cell className="font-medium">
                                            {s.label}
                                        </Cell>
                                        <Cell className="text-slate">
                                            {s.foot_length_cm
                                                ? `${s.foot_length_cm} cm`
                                                : '—'}
                                        </Cell>
                                        <Cell className="text-slate">
                                            {s.variants_count}
                                        </Cell>
                                        <Cell>
                                            <div className="flex justify-end">
                                                <button
                                                    onClick={() =>
                                                        setDeleting({
                                                            kind: 'size',
                                                            id: s.id,
                                                            label: s.label,
                                                        })
                                                    }
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
            </div>

            <ConfirmDeleteModal
                open={deleting !== null}
                title={
                    deleting?.kind === 'color'
                        ? 'Delete this colour?'
                        : 'Delete this size?'
                }
                description={`"${deleting?.label}" will be removed. Options used by existing variants cannot be deleted.`}
                deleteUrl={
                    deleting
                        ? `/admin/${deleting.kind === 'color' ? 'colors' : 'sizes'}/${deleting.id}`
                        : null
                }
                onClose={() => setDeleting(null)}
            />
        </AdminLayout>
    );
}
