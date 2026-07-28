import { Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
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
import InputError from '@/components/input-error';
import AdminLayout from '@/layouts/admin-layout';

type Variant = {
    id: number;
    color: string | null;
    size: string | null;
    price: number | null;
    effective_price: number;
    stock_quantity: number;
    status: string;
};

type Props = {
    product: { id: number; name: string; brand: string | null };
    variants: Variant[];
    options: {
        colors: { id: number; name: string; hex_code: string | null }[];
        sizes: { id: number; label: string }[];
    };
};

const field =
    'h-10 w-full rounded-[6px] border border-line bg-transparent px-3 text-sm focus:border-ink focus:outline-none';

export default function ProductVariants({ product, variants, options }: Props) {
    const [deleting, setDeleting] = useState<Variant | null>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        color_id: '',
        size_id: '',
        price: '',
        stock_quantity: '0',
    });

    function addVariant(e: React.FormEvent) {
        e.preventDefault();

        post(`/admin/products/${product.id}/variants`, {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    }

    function setStock(variant: Variant, stock: number) {
        router.patch(
            `/admin/variants/${variant.id}`,
            { stock_quantity: stock, price: variant.price },
            { preserveScroll: true },
        );
    }

    return (
        <AdminLayout
            title={`Variants — ${product.name}`}
            actions={
                <Link
                    href={`/admin/products/${product.id}/edit`}
                    className="rounded-[6px] border border-fog px-4 py-2 text-[13px] font-medium text-mist transition-colors hover:border-gold hover:text-gold"
                >
                    Edit product
                </Link>
            }
        >
            <Panel title="Add a variant">
                <form
                    onSubmit={addVariant}
                    className="grid gap-4 p-5 sm:grid-cols-2 xl:grid-cols-5 xl:items-end"
                >
                    <div className="flex flex-col gap-1.5">
                        <label className="text-[13px] font-medium">
                            Colour
                        </label>
                        <select
                            value={data.color_id}
                            onChange={(e) =>
                                setData('color_id', e.target.value)
                            }
                            className={field}
                            required
                        >
                            <option value="">Select…</option>
                            {options.colors.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                        </select>
                        <InputError message={errors.color_id} />
                    </div>

                    <div className="flex flex-col gap-1.5">
                        <label className="text-[13px] font-medium">Size</label>
                        <select
                            value={data.size_id}
                            onChange={(e) => setData('size_id', e.target.value)}
                            className={field}
                            required
                        >
                            <option value="">Select…</option>
                            {options.sizes.map((s) => (
                                <option key={s.id} value={s.id}>
                                    {s.label}
                                </option>
                            ))}
                        </select>
                        <InputError message={errors.size_id} />
                    </div>

                    <div className="flex flex-col gap-1.5">
                        <label className="text-[13px] font-medium">
                            Price override
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="Uses base price"
                            value={data.price}
                            onChange={(e) => setData('price', e.target.value)}
                            className={field}
                        />
                        <InputError message={errors.price} />
                    </div>

                    <div className="flex flex-col gap-1.5">
                        <label className="text-[13px] font-medium">Stock</label>
                        <input
                            type="number"
                            min="0"
                            value={data.stock_quantity}
                            onChange={(e) =>
                                setData('stock_quantity', e.target.value)
                            }
                            className={field}
                            required
                        />
                        <InputError message={errors.stock_quantity} />
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="h-10 rounded-[6px] bg-gold px-4 text-sm font-medium text-ink transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        Add variant
                    </button>
                </form>
            </Panel>

            <div className="mt-5">
                <Panel title={`${variants.length} variants`}>
                    <Table
                        head={[
                            'Colour',
                            'Size',
                            'Price',
                            'Stock',
                            'Status',
                            '',
                        ]}
                    >
                        {variants.length === 0 ? (
                            <EmptyRow
                                colSpan={6}
                                label="No variants yet — add one above."
                            />
                        ) : (
                            variants.map((v) => (
                                <Row key={v.id}>
                                    <Cell className="font-medium">
                                        {v.color ?? '—'}
                                    </Cell>
                                    <Cell className="text-slate">
                                        {v.size ?? '—'}
                                    </Cell>
                                    <Cell>
                                        {money(v.effective_price)}
                                        {v.price === null && (
                                            <span className="ml-1 text-xs text-slate">
                                                (base)
                                            </span>
                                        )}
                                    </Cell>
                                    <Cell>
                                        <input
                                            type="number"
                                            min="0"
                                            defaultValue={v.stock_quantity}
                                            onBlur={(e) => {
                                                const next = Number(
                                                    e.target.value,
                                                );

                                                if (next !== v.stock_quantity) {
                                                    setStock(v, next);
                                                }
                                            }}
                                            className="h-8 w-20 rounded-[6px] border border-line bg-transparent px-2 text-sm focus:border-ink focus:outline-none"
                                        />
                                    </Cell>
                                    <Cell>
                                        <Pill value={v.status} />
                                    </Cell>
                                    <Cell>
                                        <div className="flex justify-end">
                                            <button
                                                onClick={() => setDeleting(v)}
                                                className="text-[#dc3232] hover:underline"
                                            >
                                                Remove
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
                title="Remove this variant?"
                description={`${deleting?.color ?? ''} / ${deleting?.size ?? ''} will be removed from this product.`}
                deleteUrl={deleting ? `/admin/variants/${deleting.id}` : null}
                confirmLabel="Remove"
                onClose={() => setDeleting(null)}
            />
        </AdminLayout>
    );
}
