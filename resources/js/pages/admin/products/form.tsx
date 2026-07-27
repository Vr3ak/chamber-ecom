import { Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Panel } from '@/components/admin/ui';
import InputError from '@/components/input-error';
import AdminLayout from '@/layouts/admin-layout';

type Option = { id: number; name: string };

type Props = {
    product: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        base_price: number;
        brand_id: number;
        is_active: boolean;
        category_ids: number[];
        image_url: string | null;
        is_trending: boolean;
    } | null;
    options: {
        brands: Option[];
        categories: Option[];
    };
};

const field =
    'h-11 w-full rounded-[6px] border border-line bg-transparent px-3 text-sm placeholder:text-slate focus:border-ink focus:outline-none';
const labelCls = 'text-[13px] font-medium text-ink';

const slugify = (s: string) =>
    s
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

/** Create / Edit Shoe — Figma node 32:706. */
export default function ProductForm({ product, options }: Props) {
    const editing = product !== null;

    const { data, setData, post, put, processing, errors } = useForm({
        name: product?.name ?? '',
        slug: product?.slug ?? '',
        description: product?.description ?? '',
        base_price: product?.base_price?.toString() ?? '',
        brand_id: product?.brand_id?.toString() ?? '',
        is_active: product?.is_active ?? true,
        category_ids: product?.category_ids ?? ([] as number[]),
        image: null as File | null,
        is_trending: product?.is_trending ?? false,
    });

    const [imagePreview, setImagePreview] = useState<string | null>(
        product?.image_url ?? null,
    );

    function handleImageChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0] ?? null;
        setData('image', file);
        setImagePreview(
            file ? URL.createObjectURL(file) : (product?.image_url ?? null),
        );
    }

    // Slug follows the name until the product exists; after that it's a real
    // URL that other things may link to, so it's left alone.
    useEffect(() => {
        if (!editing) {
            setData('slug', slugify(data.name));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.name, editing]);

    function submit(e: React.FormEvent) {
        e.preventDefault();

        if (editing) {
            put(`/admin/products/${product.id}`, { preserveScroll: true });
        } else {
            post('/admin/products');
        }
    }

    function toggleCategory(id: number) {
        setData(
            'category_ids',
            data.category_ids.includes(id)
                ? data.category_ids.filter((c) => c !== id)
                : [...data.category_ids, id],
        );
    }

    return (
        <AdminLayout title={editing ? `Edit — ${product.name}` : 'Add Shoe'}>
            <form onSubmit={submit} className="flex max-w-3xl flex-col gap-5">
                <Panel title="Details">
                    <div className="flex flex-col gap-4 p-5">
                        <div className="flex flex-col gap-1.5">
                            <label className={labelCls} htmlFor="name">
                                Name
                            </label>
                            <input
                                id="name"
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                className={field}
                                required
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="flex flex-col gap-1.5">
                            <label className={labelCls} htmlFor="slug">
                                Slug
                            </label>
                            <input
                                id="slug"
                                value={data.slug}
                                onChange={(e) =>
                                    setData('slug', e.target.value)
                                }
                                className={field}
                                required
                            />
                            <InputError message={errors.slug} />
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="flex flex-col gap-1.5">
                                <label className={labelCls} htmlFor="brand_id">
                                    Brand
                                </label>
                                <select
                                    id="brand_id"
                                    value={data.brand_id}
                                    onChange={(e) =>
                                        setData('brand_id', e.target.value)
                                    }
                                    className={field}
                                    required
                                >
                                    <option value="">Select a brand</option>
                                    {options.brands.map((b) => (
                                        <option key={b.id} value={b.id}>
                                            {b.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.brand_id} />
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <label
                                    className={labelCls}
                                    htmlFor="base_price"
                                >
                                    Base price (USD)
                                </label>
                                <input
                                    id="base_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={data.base_price}
                                    onChange={(e) =>
                                        setData('base_price', e.target.value)
                                    }
                                    className={field}
                                    required
                                />
                                <InputError message={errors.base_price} />
                            </div>
                        </div>

                        <div className="flex flex-col gap-1.5">
                            <label className={labelCls} htmlFor="description">
                                Description
                            </label>
                            <textarea
                                id="description"
                                rows={5}
                                value={data.description ?? ''}
                                onChange={(e) =>
                                    setData('description', e.target.value)
                                }
                                className="w-full rounded-[6px] border border-line bg-transparent p-3 text-sm placeholder:text-slate focus:border-ink focus:outline-none"
                            />
                            <InputError message={errors.description} />
                        </div>

                        <div className="flex flex-col gap-1.5">
                            <label className={labelCls} htmlFor="image">
                                Image
                            </label>
                            {imagePreview && (
                                <img
                                    src={imagePreview}
                                    alt="Shoe preview"
                                    className="h-32 w-32 rounded-[6px] border border-line object-cover"
                                />
                            )}
                            <input
                                id="image"
                                type="file"
                                accept="image/*"
                                onChange={handleImageChange}
                                className="text-sm text-ink file:mr-3 file:rounded-[6px] file:border file:border-line file:bg-transparent file:px-3 file:py-1.5 file:text-sm file:font-medium"
                            />
                            <InputError message={errors.image} />
                        </div>

                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={data.is_active}
                                onChange={(e) =>
                                    setData('is_active', e.target.checked)
                                }
                            />
                            Visible in the storefront
                        </label>

                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={data.is_trending}
                                onChange={(e) =>
                                    setData('is_trending', e.target.checked)
                                }
                            />
                            <span>
                                Mark as trending
                                <span className="block text-[13px] text-slate">
                                    Features it on the homepage rail and adds a
                                    Trending badge to its card.
                                </span>
                            </span>
                        </label>
                    </div>
                </Panel>

                <Panel title="Categories">
                    <div className="flex flex-wrap gap-2 p-5">
                        {options.categories.map((c) => {
                            const on = data.category_ids.includes(c.id);

                            return (
                                <button
                                    key={c.id}
                                    type="button"
                                    onClick={() => toggleCategory(c.id)}
                                    className={`rounded-[6px] border px-3 py-1.5 text-[13px] font-medium transition-colors ${on ? 'border-ink bg-ink text-white' : 'border-line text-ink hover:border-ink'}`}
                                >
                                    {c.name}
                                </button>
                            );
                        })}
                    </div>
                </Panel>

                <div className="flex items-center justify-end gap-3">
                    <Link
                        href="/admin/products"
                        className="rounded-[6px] border border-line px-4 py-2.5 text-sm font-medium transition-colors hover:border-ink"
                    >
                        Cancel
                    </Link>
                    {editing && (
                        <Link
                            href={`/admin/products/${product.id}/variants`}
                            className="rounded-[6px] border border-line px-4 py-2.5 text-sm font-medium transition-colors hover:border-ink"
                        >
                            Manage variants
                        </Link>
                    )}
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-[6px] bg-gold px-4 py-2.5 text-sm font-medium text-ink transition-opacity hover:opacity-90 disabled:opacity-50"
                    >
                        {processing
                            ? 'Saving…'
                            : editing
                              ? 'Save Changes'
                              : 'Create Shoe'}
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
