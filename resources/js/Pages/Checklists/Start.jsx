import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import EmptyState from '../../Components/UI/EmptyState';

/**
 * Checklists/Start
 *
 * Shows all active templates so the auditor can pick one to start.
 *
 * Props:
 *   templates – array of active ChecklistTemplate objects (with questions_count)
 */
export default function Start({ templates = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        template_id: '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/checklists/start');
    }

    return (
        <AppLayout>
            <Head title="Start New Checklist" />

            <div className="space-y-6 max-w-3xl">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Link
                        href="/checklists"
                        className="text-sm text-gray-500 hover:text-gray-700 transition-colors"
                    >
                        ← Back to My Checklists
                    </Link>
                </div>

                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Start New Checklist</h1>
                    <p className="mt-1 text-sm text-gray-500">
                        Select an active template below to begin a new compliance checklist.
                    </p>
                </div>

                {templates.length === 0 ? (
                    <EmptyState
                        title="No active templates"
                        description="There are no active checklist templates available right now. Please contact your administrator."
                    />
                ) : (
                    <form onSubmit={handleSubmit} className="space-y-4">
                        {errors.template_id && (
                            <p className="text-sm text-red-600">{errors.template_id}</p>
                        )}

                        <div className="grid gap-4">
                            {templates.map((template) => {
                                const selected = data.template_id === String(template.id);
                                return (
                                    <label
                                        key={template.id}
                                        className={`relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none transition-colors ${
                                            selected
                                                ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-600'
                                                : 'border-gray-200 bg-white hover:border-indigo-300'
                                        }`}
                                    >
                                        <input
                                            type="radio"
                                            name="template_id"
                                            value={template.id}
                                            checked={selected}
                                            onChange={(e) => setData('template_id', e.target.value)}
                                            className="sr-only"
                                        />
                                        <div className="flex flex-1 flex-col gap-1">
                                            <span className="text-sm font-semibold text-gray-900">
                                                {template.title}
                                            </span>
                                            {template.description && (
                                                <span className="text-sm text-gray-500 line-clamp-2">
                                                    {template.description}
                                                </span>
                                            )}
                                            <span className="text-xs text-gray-400 mt-1">
                                                {template.questions_count ?? 0} question
                                                {template.questions_count !== 1 ? 's' : ''}
                                            </span>
                                        </div>
                                        {selected && (
                                            <svg
                                                className="h-5 w-5 text-indigo-600 shrink-0 self-center ml-3"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    fillRule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                                    clipRule="evenodd"
                                                />
                                            </svg>
                                        )}
                                    </label>
                                );
                            })}
                        </div>

                        <div className="flex items-center gap-4 pt-2">
                            <button
                                type="submit"
                                disabled={!data.template_id || processing}
                                className="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                {processing ? 'Starting…' : 'Start Checklist'}
                            </button>
                            <Link
                                href="/checklists"
                                className="text-sm text-gray-500 hover:text-gray-700 transition-colors"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>
                )}
            </div>
        </AppLayout>
    );
}
