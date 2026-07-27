import { useForm } from '@inertiajs/react';
import { Star } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Props = {
    orderId: number;
    product: { product_id: number; product_name: string } | null;
    onClose: () => void;
};

// Write-a-Review modal — Figma node 48:3645.
export default function WriteReviewModal({ orderId, product, onClose }: Props) {
    const [hovered, setHovered] = useState<number | null>(null);
    const { data, setData, post, processing, errors, reset } = useForm({
        product_id: product?.product_id ?? 0,
        rating: 5,
        body: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();

        post(`/orders/${orderId}/reviews`, {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    }

    return (
        <Dialog open={product !== null} onOpenChange={(o) => !o && onClose()}>
            <DialogContent className="font-display sm:max-w-[560px]">
                <DialogHeader>
                    <DialogTitle className="text-ink">
                        Write a Review
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="flex flex-col gap-4">
                    <p className="text-sm text-slate">
                        {product?.product_name}
                    </p>

                    <div className="flex flex-col gap-1.5">
                        <span className="text-[13px] font-medium text-ink">
                            Rating
                        </span>
                        <div className="flex gap-1">
                            {[1, 2, 3, 4, 5].map((n) => (
                                <button
                                    key={n}
                                    type="button"
                                    aria-label={`${n} star${n > 1 ? 's' : ''}`}
                                    onClick={() => setData('rating', n)}
                                    onMouseEnter={() => setHovered(n)}
                                    onMouseLeave={() => setHovered(null)}
                                >
                                    <Star
                                        className={`h-6 w-6 ${n <= (hovered ?? data.rating) ? 'fill-gold text-gold' : 'text-[#d4d4d8]'}`}
                                    />
                                </button>
                            ))}
                        </div>
                        <InputError message={errors.rating} />
                    </div>

                    <div className="flex flex-col gap-1.5">
                        <label
                            htmlFor="review-body"
                            className="text-[13px] font-medium text-ink"
                        >
                            Your review
                        </label>
                        <textarea
                            id="review-body"
                            rows={4}
                            value={data.body}
                            onChange={(e) => setData('body', e.target.value)}
                            placeholder="How did they fit? How do they feel?"
                            className="w-full rounded-[6px] border border-line bg-transparent p-3 text-sm placeholder:text-slate focus:border-ink focus:outline-none"
                        />
                        <InputError message={errors.body} />
                    </div>

                    <DialogFooter>
                        <button
                            type="button"
                            onClick={onClose}
                            className="rounded-[6px] border border-line px-4 py-2.5 text-sm font-medium text-ink transition-colors hover:border-ink"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-[6px] bg-gold px-4 py-2.5 text-sm font-medium text-ink transition-opacity hover:opacity-90 disabled:opacity-50"
                        >
                            {processing ? 'Submitting…' : 'Submit Review'}
                        </button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
