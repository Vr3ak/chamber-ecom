import { router } from '@inertiajs/react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

// Destructive confirmation — Figma node 29:384 ("Delete Shoe Confirmation").
// Reused by every admin delete so the wording and weight stay consistent.
export default function ConfirmDeleteModal({
    open,
    title = 'Delete this item?',
    description,
    deleteUrl,
    confirmLabel = 'Delete',
    onClose,
}: {
    open: boolean;
    title?: string;
    description: string;
    deleteUrl: string | null;
    confirmLabel?: string;
    onClose: () => void;
}) {
    return (
        <Dialog open={open} onOpenChange={(o) => !o && onClose()}>
            <DialogContent className="font-display sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle className="text-ink">{title}</DialogTitle>
                    <DialogDescription className="text-slate">
                        {description}
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-[6px] border border-line px-4 py-2.5 text-sm font-medium text-ink transition-colors hover:border-ink"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onClick={() => {
                            if (!deleteUrl) {
                                return;
                            }

                            router.delete(deleteUrl, {
                                preserveScroll: true,
                                onFinish: onClose,
                            });
                        }}
                        className="rounded-[6px] bg-[#dc3232] px-4 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90"
                    >
                        {confirmLabel}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
