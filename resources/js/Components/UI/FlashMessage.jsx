import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';

export default function FlashMessage() {
    const { flash } = usePage().props;
    const [visible, setVisible] = useState(false);
    const [currentFlash, setCurrentFlash] = useState(null);

    useEffect(() => {
        if (flash?.success || flash?.error) {
            setCurrentFlash(flash);
            setVisible(true);

            // Auto-dismiss after 5 seconds
            const timer = setTimeout(() => {
                setVisible(false);
            }, 5000);

            return () => clearTimeout(timer);
        }
    }, [flash]);

    if (!visible || !currentFlash) {
        return null;
    }

    const isSuccess = Boolean(currentFlash.success);
    const message = currentFlash.success || currentFlash.error;

    return (
        <div
            role="alert"
            className={`flex items-center justify-between p-4 mb-4 rounded-md border ${
                isSuccess
                    ? 'bg-green-50 border-green-200 text-green-800'
                    : 'bg-red-50 border-red-200 text-red-800'
            }`}
        >
            <div className="flex items-center gap-2">
                {isSuccess ? (
                    <svg className="h-5 w-5 text-green-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clipRule="evenodd" />
                    </svg>
                ) : (
                    <svg className="h-5 w-5 text-red-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clipRule="evenodd" />
                    </svg>
                )}
                <span className="text-sm font-medium">{message}</span>
            </div>

            <button
                type="button"
                onClick={() => setVisible(false)}
                className={`ml-4 flex-shrink-0 rounded-md p-1 focus:outline-none focus:ring-2 ${
                    isSuccess
                        ? 'text-green-500 hover:bg-green-100 focus:ring-green-400'
                        : 'text-red-500 hover:bg-red-100 focus:ring-red-400'
                }`}
                aria-label="Dismiss message"
            >
                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    );
}
