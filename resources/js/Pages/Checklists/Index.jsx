import { Link, Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Badge from '../../Components/UI/Badge';
import Pagination from '../../Components/UI/Pagination';
import EmptyState from '../../Components/UI/EmptyState';

/**
 * Checklists/Index
 *
 * Props (from ChecklistWebController::index):
 *   checklists – array of checklist instance objects for the current page
 *   meta       – { current_page, last_page, per_page, total }
 */
export default function Index({ checklists = [], meta = {} }) {
    const hasResults = checklists.length > 0;

    return (
        <AppLayout>
            <Head title="My Checklists" />

            <div className="space-y-6">
                {/* Page header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">My Checklists</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Track your compliance checklist progress and submit completed instances.
                        </p>
                    </div>
                    <Link
                        href="/checklists/start"
                        className="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors whitespace-nowrap"
                    >
                        <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        Start New
                    </Link>
                </div>

                {/* Table card */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    {!hasResults ? (
                        <EmptyState
                            title="No checklists yet"
                            description="Get started by selecting an active template and beginning a new compliance checklist."
                            ctaLabel="Start New Checklist"
                            ctaHref="/checklists/start"
                        />
                    ) : (
                        <>
                            {/* Desktop table */}
                            <div className="hidden sm:block overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Template
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Status
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Completed
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Started
                                            </th>
                                            <th scope="col" className="relative px-6 py-3">
                                                <span className="sr-only">Actions</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {checklists.map((checklist) => (
                                            <tr key={checklist.id} className="hover:bg-gray-50 transition-colors">
                                                <td className="px-6 py-4">
                                                    <div className="text-sm font-medium text-gray-900">
                                                        {checklist.template?.title ?? '—'}
                                                    </div>
                                                    {checklist.template?.description && (
                                                        <div className="text-xs text-gray-400 mt-0.5 line-clamp-1">
                                                            {checklist.template.description}
                                                        </div>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <Badge status={checklist.status} />
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {checklist.completed_at
                                                        ? new Date(checklist.completed_at).toLocaleDateString(undefined, {
                                                              year: 'numeric',
                                                              month: 'short',
                                                              day: 'numeric',
                                                          })
                                                        : '—'}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {checklist.created_at
                                                        ? new Date(checklist.created_at).toLocaleDateString(undefined, {
                                                              year: 'numeric',
                                                              month: 'short',
                                                              day: 'numeric',
                                                          })
                                                        : '—'}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <Link
                                                        href={`/checklists/${checklist.id}`}
                                                        className="text-indigo-600 hover:text-indigo-800 transition-colors"
                                                    >
                                                        {checklist.status === 'draft' ? 'Continue' : 'View'}
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {/* Mobile card stack */}
                            <div className="sm:hidden divide-y divide-gray-200">
                                {checklists.map((checklist) => (
                                    <div key={checklist.id} className="px-4 py-4 space-y-2">
                                        <div className="flex items-start justify-between gap-2">
                                            <div>
                                                <p className="text-sm font-medium text-gray-900">
                                                    {checklist.template?.title ?? '—'}
                                                </p>
                                                {checklist.template?.description && (
                                                    <p className="text-xs text-gray-400 mt-0.5 line-clamp-2">
                                                        {checklist.template.description}
                                                    </p>
                                                )}
                                            </div>
                                            <Badge status={checklist.status} />
                                        </div>
                                        <div className="flex items-center justify-between text-xs text-gray-500">
                                            <span>
                                                Started:{' '}
                                                {checklist.created_at
                                                    ? new Date(checklist.created_at).toLocaleDateString(undefined, {
                                                          year: 'numeric',
                                                          month: 'short',
                                                          day: 'numeric',
                                                      })
                                                    : '—'}
                                            </span>
                                            {checklist.completed_at && (
                                                <span>
                                                    Completed:{' '}
                                                    {new Date(checklist.completed_at).toLocaleDateString(undefined, {
                                                        year: 'numeric',
                                                        month: 'short',
                                                        day: 'numeric',
                                                    })}
                                                </span>
                                            )}
                                        </div>
                                        <div className="pt-1">
                                            <Link
                                                href={`/checklists/${checklist.id}`}
                                                className="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
                                            >
                                                {checklist.status === 'draft' ? 'Continue' : 'View'}
                                            </Link>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* Pagination */}
                            <Pagination meta={meta} />
                        </>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
