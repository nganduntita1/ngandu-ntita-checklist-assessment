import { useEffect, useRef } from 'react';

export default function ConfirmModal({
    isOpen,
    onConfirm,
    onCancel,
    title = 'Confirm Action',
    message = 'Are you sure you want to proceed? This action cannot be undone.',
}) {
    const cancelButtonRef = useRef(null);
    const modalRef = useRef(null);

    // Focus the cancel button when the modal opens
    useEffect(() => {
        if (isOpen) {
            cancelButtonRef.current?.focus();
        }
    }, [isOpen]);

    // Trap focus within the modal and handle ESC key
    useEffect(() => {
        if (!isOpen) return;

        function handleKeyDown(e) {
            if (e.key === 'Escape') {
                onCancel();
                return;
            }

            if (e.key === 'Tab') {
                const focusable = modalRef.current?.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                if (!focusable || focusable.length === 0) return;

                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (e.shiftKey) {
                    if (document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    }
                } else {
                    if (document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            }
        }

        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [isOpen, onCancel]);

    if (!isOpen) return null;

    return (
        <div
            className="fixed inset-0 z-50 overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
        >
            {/* Backdrop */}
            <div
                className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                onClick={onCancel}
                aria-hidden="true"
            />

            {/* Modal panel */}
            <div className="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div
                    ref={modalRef}
                    className="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                >
                    <div className="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div className="sm:flex sm:items-start">
                            {/* Warning icon */}
                            <div className="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg
                                    className="h-6 w-6 text-red-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                                    />
                                </svg>
                            </div>

                            <div className="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3
                                    id="modal-title"
                                    className="text-base font-semibold leading-6 text-gray-900"
                                >
                                    {title}
                                </h3>
                                <div className="mt-2">
                                    <p className="text-sm text-gray-500">{message}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                        <button
                            type="button"
                            onClick={onConfirm}
                            className="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-colors"
                        >
                            Confirm
                        </button>
                        <button
                            ref={cancelButtonRef}
                            type="button"
                            onClick={onCancel}
                            className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
