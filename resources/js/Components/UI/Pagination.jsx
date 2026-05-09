import { router } from '@inertiajs/react';

export default function Pagination({ meta, links, onPageChange }) {
    if (!meta || meta.last_page <= 1) {
        return null;
    }

    function handlePageChange(url, page) {
        if (!url) return;

        if (onPageChange) {
            onPageChange(page);
            return;
        }

        router.visit(url, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    // Build page number buttons from meta
    const pages = [];
    for (let i = 1; i <= meta.last_page; i++) {
        pages.push(i);
    }

    // Find prev/next URLs from links array if provided
    const prevLink = links?.find((l) => l.label === '&laquo; Previous' || l.label === '« Previous');
    const nextLink = links?.find((l) => l.label === 'Next &raquo;' || l.label === 'Next »');

    const prevUrl = prevLink?.url ?? null;
    const nextUrl = nextLink?.url ?? null;

    // Build URL for a given page number from the links array
    function getPageUrl(page) {
        const link = links?.find((l) => String(l.label) === String(page));
        return link?.url ?? null;
    }

    return (
        <nav
            className="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6"
            aria-label="Pagination"
        >
            {/* Mobile: prev/next only */}
            <div className="flex flex-1 justify-between sm:hidden">
                <button
                    type="button"
                    onClick={() => handlePageChange(prevUrl, meta.current_page - 1)}
                    disabled={!prevUrl}
                    className="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Previous
                </button>
                <button
                    type="button"
                    onClick={() => handlePageChange(nextUrl, meta.current_page + 1)}
                    disabled={!nextUrl}
                    className="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Next
                </button>
            </div>

            {/* Desktop: full pagination */}
            <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p className="text-sm text-gray-700">
                        Showing{' '}
                        <span className="font-medium">
                            {(meta.current_page - 1) * meta.per_page + 1}
                        </span>{' '}
                        to{' '}
                        <span className="font-medium">
                            {Math.min(meta.current_page * meta.per_page, meta.total)}
                        </span>{' '}
                        of <span className="font-medium">{meta.total}</span> results
                    </p>
                </div>

                <div>
                    <nav className="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        {/* Previous button */}
                        <button
                            type="button"
                            onClick={() => handlePageChange(prevUrl, meta.current_page - 1)}
                            disabled={!prevUrl}
                            className="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
                            aria-label="Previous page"
                        >
                            <svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fillRule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clipRule="evenodd" />
                            </svg>
                        </button>

                        {/* Page number buttons */}
                        {pages.map((page) => {
                            const isActive = page === meta.current_page;
                            const pageUrl = getPageUrl(page);

                            return (
                                <button
                                    key={page}
                                    type="button"
                                    onClick={() => handlePageChange(pageUrl, page)}
                                    aria-current={isActive ? 'page' : undefined}
                                    className={`relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-20 focus:outline-offset-0 ${
                                        isActive
                                            ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600'
                                            : 'text-gray-900 hover:bg-gray-50'
                                    }`}
                                >
                                    {page}
                                </button>
                            );
                        })}

                        {/* Next button */}
                        <button
                            type="button"
                            onClick={() => handlePageChange(nextUrl, meta.current_page + 1)}
                            disabled={!nextUrl}
                            className="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
                            aria-label="Next page"
                        >
                            <svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fillRule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clipRule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </nav>
    );
}
