import { useForm, Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import InputField from '../../Components/Forms/InputField';
import SelectField from '../../Components/Forms/SelectField';
import TextareaField from '../../Components/Forms/TextareaField';
import QuestionBuilder from '../../Components/Questions/QuestionBuilder';
import ConfirmModal from '../../Components/UI/ConfirmModal';
import { useState } from 'react';

const STATUS_OPTIONS = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

/**
 * Normalise questions coming from the server so each has a stable `_key`
 * for React's list reconciliation.
 */
function normaliseQuestions(questions = []) {
    return questions.map((q) => ({
        ...q,
        _key: q._key ?? String(q.id ?? crypto.randomUUID()),
    }));
}

/**
 * Templates/Edit
 *
 * Props (from TemplateWebController::edit):
 *   template – { id, title, description, status, questions: [...] }
 */
export default function Edit({ template }) {
    const [showDeleteModal, setShowDeleteModal] = useState(false);

    const { data, setData, put, processing, errors } = useForm({
        title: template.title ?? '',
        description: template.description ?? '',
        status: template.status ?? 'active',
        questions: normaliseQuestions(template.questions ?? []),
    });

    function handleSubmit(e) {
        e.preventDefault();
        put(`/templates/${template.id}`);
    }

    function handleDelete() {
        router.delete(`/templates/${template.id}`, {
            onFinish: () => setShowDeleteModal(false),
        });
    }

    return (
        <AppLayout>
            <Head title={`Edit — ${template.title}`} />

            <div className="max-w-3xl space-y-6">
                {/* Page header */}
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <nav className="flex items-center gap-2 text-sm text-gray-500 mb-1" aria-label="Breadcrumb">
                            <Link href="/templates" className="hover:text-indigo-600 transition-colors">
                                Templates
                            </Link>
                            <svg className="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fillRule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clipRule="evenodd" />
                            </svg>
                            <span className="text-gray-900 font-medium truncate max-w-xs">{template.title}</span>
                        </nav>
                        <h1 className="text-2xl font-bold text-gray-900">Edit Template</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Update the template details and questions. Changes are saved immediately on submit.
                        </p>
                    </div>

                    {/* Danger zone — delete from edit page */}
                    <button
                        type="button"
                        onClick={() => setShowDeleteModal(true)}
                        className="flex-shrink-0 inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-red-300 hover:bg-red-50 transition-colors"
                    >
                        <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fillRule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clipRule="evenodd" />
                        </svg>
                        Delete
                    </button>
                </div>

                <form onSubmit={handleSubmit} noValidate className="space-y-8">
                    {/* Template details card */}
                    <div className="bg-white rounded-lg border border-gray-200 shadow-sm divide-y divide-gray-100">
                        <div className="px-6 py-4">
                            <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                                Template Details
                            </h2>
                        </div>
                        <div className="px-6 py-5 space-y-5">
                            <InputField
                                label="Title"
                                name="title"
                                value={data.title}
                                onChange={(e) => setData('title', e.target.value)}
                                error={errors.title}
                                placeholder="e.g. ISO 27001 Annual Audit"
                                maxLength={255}
                                required
                            />

                            <TextareaField
                                label="Description (optional)"
                                name="description"
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                error={errors.description}
                                placeholder="Briefly describe the purpose of this checklist…"
                                rows={3}
                            />

                            <SelectField
                                label="Status"
                                name="status"
                                value={data.status}
                                onChange={(e) => setData('status', e.target.value)}
                                error={errors.status}
                                options={STATUS_OPTIONS}
                            />
                        </div>
                    </div>

                    {/* Questions card */}
                    <div className="bg-white rounded-lg border border-gray-200 shadow-sm divide-y divide-gray-100">
                        <div className="px-6 py-4">
                            <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                                Questions
                            </h2>
                        </div>
                        <div className="px-6 py-5">
                            <QuestionBuilder
                                questions={data.questions}
                                onChange={(questions) => setData('questions', questions)}
                                errors={errors}
                            />
                        </div>
                    </div>

                    {/* Form actions */}
                    <div className="flex items-center justify-end gap-3 pt-2">
                        <Link
                            href="/templates"
                            className="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {processing ? 'Saving…' : 'Save Changes'}
                        </button>
                    </div>
                </form>
            </div>

            {/* Delete confirmation modal */}
            <ConfirmModal
                isOpen={showDeleteModal}
                title="Delete Template"
                message={`Are you sure you want to delete "${template.title}"? This will permanently remove the template and all its questions. This action cannot be undone.`}
                onConfirm={handleDelete}
                onCancel={() => setShowDeleteModal(false)}
            />
        </AppLayout>
    );
}
