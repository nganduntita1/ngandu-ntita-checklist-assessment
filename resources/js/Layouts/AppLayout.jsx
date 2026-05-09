import { useState } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import FlashMessage from '../Components/UI/FlashMessage';

export default function AppLayout({ children }) {
    const { auth } = usePage().props;
    const user = auth?.user;
    const isAdmin = user?.role === 'admin';
    const isAuditor = user?.role === 'auditor';

    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    function handleLogout(e) {
        e.preventDefault();
        router.post('/logout');
    }

    return (
        <div className="min-h-screen bg-gray-100">
            {/* Navigation */}
            <nav className="bg-white shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        {/* Logo + Desktop Nav */}
                        <div className="flex">
                            <div className="flex-shrink-0 flex items-center">
                                <Link href="/dashboard" className="text-xl font-bold text-indigo-600">
                                    Compliance
                                </Link>
                            </div>

                            {/* Desktop links */}
                            <div className="hidden md:ml-6 md:flex md:space-x-4 md:items-center">
                                <Link
                                    href="/dashboard"
                                    className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition-colors"
                                >
                                    Dashboard
                                </Link>

                                {isAdmin && (
                                    <>
                                        <Link
                                            href="/templates"
                                            className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition-colors"
                                        >
                                            Templates
                                        </Link>
                                        <Link
                                            href="/reports"
                                            className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition-colors"
                                        >
                                            Reports
                                        </Link>
                                    </>
                                )}

                                {isAuditor && (
                                    <Link
                                        href="/checklists"
                                        className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition-colors"
                                    >
                                        Checklists
                                    </Link>
                                )}
                            </div>
                        </div>

                        {/* Desktop user menu */}
                        <div className="hidden md:flex md:items-center md:space-x-4">
                            <span className="text-sm text-gray-500">
                                {user?.name}
                                <span className="ml-1 text-xs text-gray-400 capitalize">({user?.role})</span>
                            </span>
                            <form onSubmit={handleLogout}>
                                <button
                                    type="submit"
                                    className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-red-600 hover:bg-gray-50 transition-colors"
                                >
                                    Logout
                                </button>
                            </form>
                        </div>

                        {/* Mobile hamburger */}
                        <div className="flex items-center md:hidden">
                            <button
                                type="button"
                                onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                                className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                                aria-expanded={mobileMenuOpen}
                                aria-label="Toggle navigation menu"
                            >
                                {mobileMenuOpen ? (
                                    /* X icon */
                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                ) : (
                                    /* Hamburger icon */
                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                    </svg>
                                )}
                            </button>
                        </div>
                    </div>
                </div>

                {/* Mobile menu */}
                {mobileMenuOpen && (
                    <div className="md:hidden border-t border-gray-200">
                        <div className="px-2 pt-2 pb-3 space-y-1">
                            <Link
                                href="/dashboard"
                                className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50"
                                onClick={() => setMobileMenuOpen(false)}
                            >
                                Dashboard
                            </Link>

                            {isAdmin && (
                                <>
                                    <Link
                                        href="/templates"
                                        className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50"
                                        onClick={() => setMobileMenuOpen(false)}
                                    >
                                        Templates
                                    </Link>
                                    <Link
                                        href="/reports"
                                        className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50"
                                        onClick={() => setMobileMenuOpen(false)}
                                    >
                                        Reports
                                    </Link>
                                </>
                            )}

                            {isAuditor && (
                                <Link
                                    href="/checklists"
                                    className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50"
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    Checklists
                                </Link>
                            )}
                        </div>

                        <div className="border-t border-gray-200 px-2 pt-2 pb-3">
                            <div className="px-3 py-2 text-sm text-gray-500">
                                {user?.name}
                                <span className="ml-1 text-xs text-gray-400 capitalize">({user?.role})</span>
                            </div>
                            <form onSubmit={handleLogout}>
                                <button
                                    type="submit"
                                    className="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-red-600 hover:bg-gray-50"
                                >
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                )}
            </nav>

            {/* Flash messages */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <FlashMessage />
            </div>

            {/* Page content */}
            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                {children}
            </main>
        </div>
    );
}
