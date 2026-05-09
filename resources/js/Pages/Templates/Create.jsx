import { useForm, Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import InputField from '../../Components/Forms/InputField';
import SelectField from '../../Components/Forms/SelectField';
import TextareaField from '../../Components/Forms/TextareaField';
import QuestionBuilder from '../../Components/Questions/QuestionBuilder';

const STATUS_OPTIONS = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

const DEFAULT_QUESTION = () => ({
    _key: crypto.randomUUID(),
    question_text: '',
    answer_type: 'text',
    required: true,
    sort_order: 0,
});

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        description: '',
        status: 'active',
        questions: [DEFAULT_QUESTION()],
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/templates');
    }

    return (
        <AppLayout>
            <Head title="New Template" />

            <div className="max-w-3xl space-y-6">
                {/* Page header */}
                <div>
                    <nav className="flex items-center gap-2 text-sm text-gray-500 mb-1" aria-label="Breadcrumb">
                        <Link href="/templates" className="hover:text-indigo-600 transition-colors">
                            Templates
                        </Link>
                        <svg className="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fillRule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clipRule="evenodd" />
                        </svg>
                        <span className="text-gray-900 font-medium">New Template</span>
                    </nav>
                    <h1 className="text-2xl font-bold text-gray-900">New Template</h1>
                    <p className="mt-1 text-sm text-gray-500">
                        Define a reusable checklist template with typed questions for auditors to complete.
                    </p>
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
                                autoFocus
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
                            {processing ? 'Creating…' : 'Create Template'}
                        </button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
