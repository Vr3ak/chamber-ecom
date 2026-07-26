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

// Figma draws the card two ways: borderless on the homepage grid
// (node 29:39) and framed with a hairline border on the category listing
// (node 32:852), where it sits against the same #eee page fill and needs
// the edge to read as a card.
export default function ProductCard({
    product,
    variant = 'plain',
}: {
    product: ProductSummary;
    variant?: 'plain' | 'framed';
}) {
    const price = product.from_price ?? product.base_price ?? 0;
    const framed = variant === 'framed';

    return (
        <Link
            href={`/products/${product.slug}`}
            className={
                framed
                    ? 'group flex flex-col overflow-hidden rounded-lg border border-line'
                    : 'group flex flex-col gap-2'
            }
        >
            {/* Figma sizes the tile 304×260 (node 29:40) rather than square. */}
            <div
                className={`flex aspect-[304/260] items-center justify-center overflow-hidden bg-gradient-to-br ${framed ? '' : 'rounded-[6px]'} ${tileGradient(product.id)}`}
            >
                <span className="px-3 text-center text-base font-semibold text-white/90 drop-shadow">
                    {product.name}
                </span>
            </div>
            <div className={`flex flex-col gap-1 ${framed ? 'p-3' : 'pt-3'}`}>
                <p className="text-[15px] font-medium text-ink transition-colors group-hover:text-gold">
                    {product.name}
                </p>
                {product.brand && (
                    <p className="text-xs text-slate">{product.brand}</p>
                )}
                <div className="flex items-center gap-2 text-sm text-ink">
                    <span className="font-medium">${price.toFixed(2)}</span>
                    {product.avg_rating !== null && (
                        <span className="inline-flex items-center gap-0.5 text-xs text-dim">
                            <Star className="h-3 w-3 fill-gold text-gold" />
                            {product.avg_rating}
                        </span>
                    )}
                </div>
            </div>
        </Link>
    );
}
