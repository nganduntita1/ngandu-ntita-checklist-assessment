/**
 * AnswerInput
 *
 * Renders the correct input type based on the question's `answer_type`.
 *
 * Props:
 *   question  – { id, question_text, answer_type, required, sort_order }
 *   value     – current answer value (string for text/textarea/number, boolean for boolean)
 *   onChange  – (value: string | boolean) => void
 *   error     – optional error message string
 *   readOnly  – when true, renders a read-only display instead of an editable input
 */
export default function AnswerInput({ question, value, onChange, error, readOnly = false }) {
    const inputId = `answer-${question.id}`;
    const errorId = `answer-error-${question.id}`;

    const baseInputClasses = `block w-full rounded-md border px-3 py-2 text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors ${
        error
            ? 'border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500'
            : 'border-gray-300 text-gray-900'
    }`;

    const readOnlyClasses =
        'block w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700';

    // ── boolean ──────────────────────────────────────────────────────────────
    if (question.answer_type === 'boolean') {
        const isChecked = value === true || value === 'true' || value === '1';

        if (readOnly) {
            return (
                <div>
                    <span
                        className={`inline-flex items-center gap-1.5 text-sm font-medium ${
                            isChecked ? 'text-green-700' : 'text-gray-500'
                        }`}
                    >
                        {isChecked ? (
                            <>
                                <svg className="h-4 w-4 text-green-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fillRule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clipRule="evenodd" />
                                </svg>
                                Yes
                            </>
                        ) : (
                            <>
                                <svg className="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                                No
                            </>
                        )}
                    </span>
                </div>
            );
        }

        return (
            <div>
                <div className="flex items-center gap-3">
                    {/* Toggle switch */}
                    <button
                        type="button"
                        role="switch"
                        id={inputId}
                        aria-checked={isChecked}
                        aria-describedby={error ? errorId : undefined}
                        onClick={() => onChange(!isChecked)}
                        className={`relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 ${
                            isChecked ? 'bg-indigo-600' : 'bg-gray-200'
                        }`}
                    >
                        <span
                            aria-hidden="true"
                            className={`pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${
                                isChecked ? 'translate-x-5' : 'translate-x-0'
                            }`}
                        />
                    </button>
                    <label
                        htmlFor={inputId}
                        className="text-sm font-medium text-gray-700 cursor-pointer select-none"
                    >
                        {isChecked ? 'Yes' : 'No'}
                    </label>
                </div>
                {error && (
                    <p id={errorId} className="mt-1 text-sm text-red-600" role="alert">
                        {error}
                    </p>
                )}
            </div>
        );
    }

    // ── textarea ──────────────────────────────────────────────────────────────
    if (question.answer_type === 'textarea') {
        if (readOnly) {
            return (
                <div>
                    <p className={readOnlyClasses + ' whitespace-pre-wrap min-h-[4rem]'}>
                        {value || <span className="text-gray-400 italic">No answer provided</span>}
                    </p>
                </div>
            );
        }

        return (
            <div>
                <textarea
                    id={inputId}
                    value={value ?? ''}
                    onChange={(e) => onChange(e.target.value)}
                    rows={4}
                    aria-describedby={error ? errorId : undefined}
                    aria-invalid={error ? 'true' : undefined}
                    className={baseInputClasses + ' resize-y'}
                />
                {error && (
                    <p id={errorId} className="mt-1 text-sm text-red-600" role="alert">
                        {error}
                    </p>
                )}
            </div>
        );
    }

    // ── number ────────────────────────────────────────────────────────────────
    if (question.answer_type === 'number') {
        if (readOnly) {
            return (
                <div>
                    <p className={readOnlyClasses}>
                        {value !== '' && value !== null && value !== undefined
                            ? value
                            : <span className="text-gray-400 italic">No answer provided</span>}
                    </p>
                </div>
            );
        }

        return (
            <div>
                <input
                    id={inputId}
                    type="number"
                    value={value ?? ''}
                    onChange={(e) => onChange(e.target.value)}
                    aria-describedby={error ? errorId : undefined}
                    aria-invalid={error ? 'true' : undefined}
                    className={baseInputClasses}
                />
                {error && (
                    <p id={errorId} className="mt-1 text-sm text-red-600" role="alert">
                        {error}
                    </p>
                )}
            </div>
        );
    }

    // ── text (default) ────────────────────────────────────────────────────────
    if (readOnly) {
        return (
            <div>
                <p className={readOnlyClasses}>
                    {value || <span className="text-gray-400 italic">No answer provided</span>}
                </p>
            </div>
        );
    }

    return (
        <div>
            <input
                id={inputId}
                type="text"
                value={value ?? ''}
                onChange={(e) => onChange(e.target.value)}
                aria-describedby={error ? errorId : undefined}
                aria-invalid={error ? 'true' : undefined}
                className={baseInputClasses}
            />
            {error && (
                <p id={errorId} className="mt-1 text-sm text-red-600" role="alert">
                    {error}
                </p>
            )}
        </div>
    );
}
