import { Link } from '@inertiajs/react';
import { Star } from 'lucide-react';

export type ProductSummary = {
    id: number;
    name: string;
    slug: string;
    brand?: string;
    base_price?: number;
    from_price?: number;
    avg_rating: number | null;
    reviews_count: number;
};

// Gradient tiles stand in for product photos (the seed ships placeholder
// image paths, not real files). Keyed by product id so a shoe keeps the
// same colour across every page.
const TILE_GRADIENTS = [
    'from-orange-300 to-rose-400',
    'from-sky-300 to-indigo-400',
    'from-emerald-300 to-teal-400',
    'from-fuchsia-300 to-purple-400',
    'from-amber-300 to-orange-400',
    'from-violet-300 to-fuchsia-400',
    'from-cyan-300 to-blue-400',
    'from-lime-300 to-emerald-400',
];

export function tileGradient(id: number): string {
    return TILE_GRADIENTS[id % TILE_GRADIENTS.length];
}

export default function ProductCard({ product }: { product: ProductSummary }) {
    const price = product.from_price ?? product.base_price ?? 0;

    return (
        <Link
            href={`/products/${product.slug}`}
            className="group flex flex-col gap-2"
        >
            <div
                className={`flex aspect-square items-center justify-center overflow-hidden rounded-md bg-gradient-to-br ${tileGradient(product.id)}`}
            >
                <span className="px-3 text-center text-base font-semibold text-white/90 drop-shadow">
                    {product.name}
                </span>
            </div>
            <div className="pt-2">
                <p className="text-[15px] font-medium text-[#222831] group-hover:text-[#ffd369]">
                    {product.name}
                </p>
                {product.brand && (
                    <p className="text-xs text-[#808080]">{product.brand}</p>
                )}
                <div className="mt-1 flex items-center gap-2 text-sm text-[#393e46]">
                    <span className="font-medium">${price.toFixed(2)}</span>
                    {product.avg_rating !== null && (
                        <span className="inline-flex items-center gap-0.5 text-xs text-[#808080]">
                            <Star className="h-3 w-3 fill-[#ffd369] text-[#ffd369]" />
                            {product.avg_rating}
                        </span>
                    )}
                </div>
            </div>
        </Link>
    );
}
