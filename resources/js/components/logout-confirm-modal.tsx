import { router } from '@inertiajs/react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { logout } from '@/routes';

// Logout confirmation — Figma node 102:4873.
export default function LogoutConfirmModal({
    open,
    onClose,
}: {
    open: boolean;
    onClose: () => void;
}) {
    return (
        <Dialog open={open} onOpenChange={(o) => !o && onClose()}>
            <DialogContent className="font-display sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle className="text-ink">Log out?</DialogTitle>
                    <DialogDescription className="text-slate">
                        You'll need to sign in again to see your cart, wishlist
                        and orders.
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
                        onClick={() => router.post(logout().url)}
                        className="rounded-[6px] bg-[#dc3232] px-4 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90"
                        data-test="confirm-logout-button"
                    >
                        Log Out
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
