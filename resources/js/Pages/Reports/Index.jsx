import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Badge from '../../Components/UI/Badge';
import Pagination from '../../Components/UI/Pagination';
import EmptyState from '../../Components/UI/EmptyState';

/**
 * Reports/Index
 *
 * Props (from ReportWebController::index):
 *   reports   – array of report objects for the current page
 *   meta      – { current_page, last_page, per_page, total }
 *   filters   – { date_from?, date_to?, template_id?, auditor_id? }
 *   templates – array of { id, title } for the template select
 *   auditors  – array of { id, name } for the auditor select
 */
export default function Index({
    reports = [],
    meta = {},
    filters = {},
    templates = [],
    auditors = [],
}) {
    const [form, setForm] = useState({
        date_from:   filters.date_from   ?? '',
        date_to:     filters.date_to     ?? '',
        template_id: filters.template_id ?? '',
        auditor_id:  filters.auditor_id  ?? '',
    });

    const hasResults = reports.length > 0;
    const hasActiveFilters =
        form.date_from || form.date_to || form.template_id || form.auditor_id;

    function handleChange(e) {
        setForm((prev) => ({ ...prev, [e.target.name]: e.target.value }));
    }

    function handleApply(e) {
        e.preventDefault();
        router.get(
            '/reports',
            {
                date_from:   form.date_from   || undefined,
                date_to:     form.date_to     || undefined,
                template_id: form.template_id || undefined,
                auditor_id:  form.auditor_id  || undefined,
            },
            { preserveState: true, replace: true }
        );
    }

    function handleClear() {
        const cleared = { date_from: '', date_to: '', template_id: '', auditor_id: '' };
        setForm(cleared);
        router.get('/reports', {}, { preserveState: false, replace: true });
    }

    function formatDate(dateString) {
        if (!dateString) return '—';
        return new Date(dateString).toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }

    return (
        <AppLayout>
            <Head title="Reports" />

            <div className="space-y-6">
                {/* Page header */}
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Reports</h1>
                    <p className="mt-1 text-sm text-gray-500">
                        View and filter all compliance checklist submissions across the organisation.
                    </p>
                </div>

                {/* Filter bar */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <form onSubmit={handleApply}>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            {/* Date from */}
                            <div>
                                <label
                                    htmlFor="date_from"
                                    className="block text-xs font-medium text-gray-700 mb-1"
                                >
                                    Date from
                                </label>
                                <input
                                    id="date_from"
                                    name="date_from"
                                    type="date"
                                    value={form.date_from}
                                    onChange={handleChange}
                                    className="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                                />
                            </div>

                            {/* Date to */}
                            <div>
                                <label
                                    htmlFor="date_to"
                                    className="block text-xs font-medium text-gray-700 mb-1"
                                >
                                    Date to
                                </label>
                                <input
                                    id="date_to"
                                    name="date_to"
                                    type="date"
                                    value={form.date_to}
                                    onChange={handleChange}
                                    min={form.date_from || undefined}
                                    className="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                                />
                            </div>

                            {/* Template select */}
                            <div>
                                <label
                                    htmlFor="template_id"
                                    className="block text-xs font-medium text-gray-700 mb-1"
                                >
                                    Template
                                </label>
                                <select
                                    id="template_id"
                                    name="template_id"
                                    value={form.template_id}
                                    onChange={handleChange}
                                    className="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                                >
                                    <option value="">All templates</option>
                                    {templates.map((t) => (
                                        <option key={t.id} value={t.id}>
                                            {t.title}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* Auditor select */}
                            <div>
                                <label
                                    htmlFor="auditor_id"
                                    className="block text-xs font-medium text-gray-700 mb-1"
                                >
                                    Auditor
                                </label>
                                <select
                                    id="auditor_id"
                                    name="auditor_id"
                                    value={form.auditor_id}
                                    onChange={handleChange}
                                    className="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                                >
                                    <option value="">All auditors</option>
                                    {auditors.map((a) => (
                                        <option key={a.id} value={a.id}>
                                            {a.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        {/* Filter actions */}
                        <div className="mt-4 flex items-center gap-3">
                            <button
                                type="submit"
                                className="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors"
                            >
                                <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fillRule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clipRule="evenodd" />
                                </svg>
                                Apply Filters
                            </button>

                            {hasActiveFilters && (
                                <button
                                    type="button"
                                    onClick={handleClear}
                                    className="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-500 transition-colors"
                                >
                                    <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                    </svg>
                                    Clear Filters
                                </button>
                            )}
                        </div>
                    </form>
                </div>

                {/* Results table */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    {!hasResults ? (
                        <EmptyState
                            title={hasActiveFilters ? 'No results match your filters' : 'No reports yet'}
                            description={
                                hasActiveFilters
                                    ? 'Try adjusting or clearing the filters to see more results.'
                                    : 'Reports will appear here once auditors start completing checklists.'
                            }
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
                                                Auditor
                                            </th>
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
                                                Created
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {reports.map((report) => (
                                            <tr key={report.id} className="hover:bg-gray-50 transition-colors">
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {report.auditor?.name ?? '—'}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                    {report.template?.title ?? '—'}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <Badge status={report.status} />
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {formatDate(report.completed_at)}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {formatDate(report.created_at)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {/* Mobile card stack */}
                            <div className="sm:hidden divide-y divide-gray-200">
                                {reports.map((report) => (
                                    <div key={report.id} className="px-4 py-4 space-y-2">
                                        <div className="flex items-start justify-between gap-2">
                                            <div>
                                                <p className="text-sm font-medium text-gray-900">
                                                    {report.auditor?.name ?? '—'}
                                                </p>
                                                <p className="text-xs text-gray-500 mt-0.5">
                                                    {report.template?.title ?? '—'}
                                                </p>
                                            </div>
                                            <Badge status={report.status} />
                                        </div>
                                        <div className="flex items-center justify-between text-xs text-gray-500">
                                            <span>Created: {formatDate(report.created_at)}</span>
                                            {report.completed_at && (
                                                <span>Completed: {formatDate(report.completed_at)}</span>
                                            )}
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
