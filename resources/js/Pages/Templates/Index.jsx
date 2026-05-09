import { useState, useCallback } from 'react';
import { Link, router, Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Badge from '../../Components/UI/Badge';
import Pagination from '../../Components/UI/Pagination';
import EmptyState from '../../Components/UI/EmptyState';
import ConfirmModal from '../../Components/UI/ConfirmModal';

/**
 * Templates/Index
 *
 * Props (from TemplateWebController::index):
 *   templates  – array of template objects for the current page
 *   meta       – { current_page, last_page, per_page, total }
 *   filters    – { search?, page? }
 */
export default function Index({ templates = [], meta = {}, filters = {} }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [deleteTarget, setDeleteTarget] = useState(null); // { id, title }

    // Debounced search — fires a new Inertia visit after the user stops typing
    const handleSearchChange = useCallback((e) => {
        const value = e.target.value;
        setSearch(value);

        router.get(
            '/templates',
            { search: value || undefined, page: 1 },
            { preserveState: true, replace: true }
        );
    }, []);

    function handleDelete() {
        if (!deleteTarget) return;
        router.delete(`/templates/${deleteTarget.id}`, {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        });
    }

    const hasResults = templates.length > 0;

    return (
        <AppLayout>
            <Head title="Templates" />

            <div className="space-y-6">
                {/* Page header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Checklist Templates</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Manage the compliance templates that auditors use to complete checklists.
                        </p>
                    </div>
                    <Link
                        href="/templates/create"
                        className="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors whitespace-nowrap"
                    >
                        <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        New Template
                    </Link>
                </div>

                {/* Search bar */}
                <div className="relative max-w-sm">
                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg className="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fillRule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clipRule="evenodd" />
                        </svg>
                    </div>
                    <input
                        type="search"
                        value={search}
                        onChange={handleSearchChange}
                        placeholder="Search templates…"
                        aria-label="Search templates"
                        className="block w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                    />
                </div>

                {/* Table card */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    {!hasResults ? (
                        <EmptyState
                            title={search ? 'No templates match your search' : 'No templates yet'}
                            description={
                                search
                                    ? 'Try a different search term or clear the search to see all templates.'
                                    : 'Get started by creating your first checklist template.'
                            }
                            ctaLabel={search ? undefined : 'New Template'}
                            ctaHref={search ? undefined : '/templates/create'}
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
                                                <span className="inline-flex items-center gap-1">
                                                    Title
                                                    {/* Sort indicator — currently always desc by created_at */}
                                                    <svg className="h-3.5 w-3.5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fillRule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clipRule="evenodd" />
                                                    </svg>
                                                </span>
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
                                                Questions
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Created
                                            </th>
                                            <th scope="col" className="relative px-6 py-3">
                                                <span className="sr-only">Actions</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {templates.map((template) => (
                                            <tr key={template.id} className="hover:bg-gray-50 transition-colors">
                                                <td className="px-6 py-4">
                                                    <div className="text-sm font-medium text-gray-900">
                                                        {template.title}
                                                    </div>
                                                    {template.description && (
                                                        <div className="text-xs text-gray-400 mt-0.5 line-clamp-1">
                                                            {template.description}
                                                        </div>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <Badge status={template.status} />
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {template.questions_count ?? 0}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {template.created_at
                                                        ? new Date(template.created_at).toLocaleDateString(undefined, {
                                                              year: 'numeric',
                                                              month: 'short',
                                                              day: 'numeric',
                                                          })
                                                        : '—'}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div className="flex items-center justify-end gap-3">
                                                        <Link
                                                            href={`/templates/${template.id}/edit`}
                                                            className="text-indigo-600 hover:text-indigo-800 transition-colors"
                                                        >
                                                            Edit
                                                        </Link>
                                                        <button
                                                            type="button"
                                                            onClick={() => setDeleteTarget(template)}
                                                            className="text-red-600 hover:text-red-800 transition-colors"
                                                        >
                                                            Delete
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {/* Mobile card stack */}
                            <div className="sm:hidden divide-y divide-gray-200">
                                {templates.map((template) => (
                                    <div key={template.id} className="px-4 py-4 space-y-2">
                                        <div className="flex items-start justify-between gap-2">
                                            <div>
                                                <p className="text-sm font-medium text-gray-900">{template.title}</p>
                                                {template.description && (
                                                    <p className="text-xs text-gray-400 mt-0.5 line-clamp-2">
                                                        {template.description}
                                                    </p>
                                                )}
                                            </div>
                                            <Badge status={template.status} />
                                        </div>
                                        <div className="flex items-center justify-between text-xs text-gray-500">
                                            <span>{template.questions_count ?? 0} questions</span>
                                            {template.created_at && (
                                                <span>
                                                    {new Date(template.created_at).toLocaleDateString(undefined, {
                                                        year: 'numeric',
                                                        month: 'short',
                                                        day: 'numeric',
                                                    })}
                                                </span>
                                            )}
                                        </div>
                                        <div className="flex items-center gap-4 pt-1">
                                            <Link
                                                href={`/templates/${template.id}/edit`}
                                                className="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                type="button"
                                                onClick={() => setDeleteTarget(template)}
                                                className="text-sm text-red-600 hover:text-red-800 font-medium transition-colors"
                                            >
                                                Delete
                                            </button>
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

            {/* Delete confirmation modal */}
            <ConfirmModal
                isOpen={!!deleteTarget}
                title="Delete Template"
                message={
                    deleteTarget
                        ? `Are you sure you want to delete "${deleteTarget.title}"? This will permanently remove the template and all its questions. This action cannot be undone.`
                        : ''
                }
                onConfirm={handleDelete}
                onCancel={() => setDeleteTarget(null)}
            />
        </AppLayout>
    );
}
