import InputField from '../Forms/InputField';
import SelectField from '../Forms/SelectField';

const ANSWER_TYPE_OPTIONS = [
    { value: 'text', label: 'Text' },
    { value: 'textarea', label: 'Textarea' },
    { value: 'boolean', label: 'Boolean (Yes/No)' },
    { value: 'number', label: 'Number' },
];

function newQuestion(sortOrder = 0) {
    return {
        _key: crypto.randomUUID(),
        question_text: '',
        answer_type: 'text',
        required: true,
        sort_order: sortOrder,
    };
}

/**
 * QuestionBuilder
 *
 * Props:
 *   questions  – array of question objects (managed by parent via useForm)
 *   onChange   – (updatedQuestions: array) => void
 *   errors     – flat errors object from Inertia useForm, keyed as "questions.N.field"
 */
export default function QuestionBuilder({ questions = [], onChange, errors = {} }) {
    function addQuestion() {
        const nextOrder = questions.length > 0
            ? Math.max(...questions.map((q) => q.sort_order ?? 0)) + 1
            : 0;
        onChange([...questions, newQuestion(nextOrder)]);
    }

    function removeQuestion(index) {
        const updated = questions.filter((_, i) => i !== index);
        onChange(updated);
    }

    function updateQuestion(index, field, value) {
        const updated = questions.map((q, i) =>
            i === index ? { ...q, [field]: value } : q
        );
        onChange(updated);
    }

    function moveUp(index) {
        if (index === 0) return;
        const updated = [...questions];
        [updated[index - 1], updated[index]] = [updated[index], updated[index - 1]];
        // Sync sort_order to array position
        onChange(updated.map((q, i) => ({ ...q, sort_order: i })));
    }

    function moveDown(index) {
        if (index === questions.length - 1) return;
        const updated = [...questions];
        [updated[index], updated[index + 1]] = [updated[index + 1], updated[index]];
        onChange(updated.map((q, i) => ({ ...q, sort_order: i })));
    }

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between">
                <h3 className="text-sm font-semibold text-gray-700">
                    Questions
                    <span className="ml-1.5 text-xs font-normal text-gray-400">
                        ({questions.length} {questions.length === 1 ? 'question' : 'questions'})
                    </span>
                </h3>
                <button
                    type="button"
                    onClick={addQuestion}
                    className="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors"
                >
                    <svg className="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Add Question
                </button>
            </div>

            {questions.length === 0 && (
                <div className="rounded-lg border-2 border-dashed border-gray-200 py-8 text-center">
                    <p className="text-sm text-gray-400">No questions yet. Click "Add Question" to get started.</p>
                </div>
            )}

            {/* Top-level questions array error (e.g. "at least 1 question required") */}
            {errors['questions'] && (
                <p className="text-sm text-red-600" role="alert">{errors['questions']}</p>
            )}

            <ol className="space-y-3" aria-label="Question list">
                {questions.map((question, index) => (
                    <li
                        key={question._key ?? index}
                        className="rounded-lg border border-gray-200 bg-white shadow-sm"
                    >
                        {/* Row header */}
                        <div className="flex items-center justify-between px-4 py-2 border-b border-gray-100 bg-gray-50 rounded-t-lg">
                            <span className="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                Question {index + 1}
                            </span>
                            <div className="flex items-center gap-1">
                                {/* Move up */}
                                <button
                                    type="button"
                                    onClick={() => moveUp(index)}
                                    disabled={index === 0}
                                    aria-label={`Move question ${index + 1} up`}
                                    className="p-1 rounded text-gray-400 hover:text-gray-600 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                >
                                    <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fillRule="evenodd" d="M14.77 12.79a.75.75 0 01-1.06-.02L10 8.832 6.29 12.77a.75.75 0 11-1.08-1.04l4.25-4.5a.75.75 0 011.08 0l4.25 4.5a.75.75 0 01-.02 1.06z" clipRule="evenodd" />
                                    </svg>
                                </button>
                                {/* Move down */}
                                <button
                                    type="button"
                                    onClick={() => moveDown(index)}
                                    disabled={index === questions.length - 1}
                                    aria-label={`Move question ${index + 1} down`}
                                    className="p-1 rounded text-gray-400 hover:text-gray-600 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                >
                                    <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fillRule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clipRule="evenodd" />
                                    </svg>
                                </button>
                                {/* Remove */}
                                <button
                                    type="button"
                                    onClick={() => removeQuestion(index)}
                                    aria-label={`Remove question ${index + 1}`}
                                    className="p-1 rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                >
                                    <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fillRule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clipRule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {/* Row fields */}
                        <div className="px-4 py-4 grid grid-cols-1 sm:grid-cols-12 gap-4">
                            {/* Question text — spans most of the row */}
                            <div className="sm:col-span-5">
                                <InputField
                                    label="Question text"
                                    name={`questions[${index}][question_text]`}
                                    value={question.question_text}
                                    onChange={(e) => updateQuestion(index, 'question_text', e.target.value)}
                                    error={errors[`questions.${index}.question_text`]}
                                    placeholder="e.g. Are access controls documented?"
                                />
                            </div>

                            {/* Answer type */}
                            <div className="sm:col-span-3">
                                <SelectField
                                    label="Answer type"
                                    name={`questions[${index}][answer_type]`}
                                    value={question.answer_type}
                                    onChange={(e) => updateQuestion(index, 'answer_type', e.target.value)}
                                    error={errors[`questions.${index}.answer_type`]}
                                    options={ANSWER_TYPE_OPTIONS}
                                />
                            </div>

                            {/* Sort order */}
                            <div className="sm:col-span-2">
                                <InputField
                                    label="Sort order"
                                    name={`questions[${index}][sort_order]`}
                                    type="number"
                                    value={question.sort_order}
                                    onChange={(e) =>
                                        updateQuestion(index, 'sort_order', parseInt(e.target.value, 10) || 0)
                                    }
                                    error={errors[`questions.${index}.sort_order`]}
                                    min={0}
                                />
                            </div>

                            {/* Required toggle */}
                            <div className="sm:col-span-2 flex items-end pb-0.5">
                                <div className="flex items-center gap-2">
                                    <button
                                        type="button"
                                        role="switch"
                                        id={`required-${question._key ?? index}`}
                                        aria-checked={question.required}
                                        onClick={() => updateQuestion(index, 'required', !question.required)}
                                        className={`relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 ${
                                            question.required ? 'bg-indigo-600' : 'bg-gray-200'
                                        }`}
                                    >
                                        <span
                                            aria-hidden="true"
                                            className={`pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${
                                                question.required ? 'translate-x-5' : 'translate-x-0'
                                            }`}
                                        />
                                    </button>
                                    <label
                                        htmlFor={`required-${question._key ?? index}`}
                                        className="text-sm font-medium text-gray-700 cursor-pointer select-none whitespace-nowrap"
                                    >
                                        Required
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                ))}
            </ol>

            {questions.length > 0 && (
                <button
                    type="button"
                    onClick={addQuestion}
                    className="w-full rounded-lg border-2 border-dashed border-gray-200 py-3 text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 transition-colors"
                >
                    + Add another question
                </button>
            )}
        </div>
    );
}
