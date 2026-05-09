import { useState } from 'react';
import { Link, Head, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Badge from '../../Components/UI/Badge';
import AnswerInput from '../../Components/Questions/AnswerInput';

/**
 * Build an initial answers map from the instance's existing answers.
 * Returns an object keyed by question_id → answer_value.
 */
function buildAnswersMap(questions = [], answers = []) {
    const map = {};

    // Initialise all questions with empty values
    questions.forEach((q) => {
        map[q.id] = q.answer_type === 'boolean' ? false : '';
    });

    // Overlay saved answers
    answers.forEach((a) => {
        const question = questions.find((q) => q.id === a.question_id);
        if (!question) return;

        if (question.answer_type === 'boolean') {
            map[a.question_id] = a.answer_value === 'true' || a.answer_value === '1' || a.answer_value === true;
        } else {
            map[a.question_id] = a.answer_value ?? '';
        }
    });

    return map;
}

/**
 * Serialise the answers map to the array format expected by the backend.
 */
function serialiseAnswers(answersMap) {
    return Object.entries(answersMap).map(([questionId, value]) => ({
        question_id: parseInt(questionId, 10),
        answer_value: typeof value === 'boolean' ? (value ? 'true' : 'false') : String(value ?? ''),
    }));
}

/**
 * Checklists/Show
 *
 * Props (from ChecklistWebController::show):
 *   checklist – {
 *     id, status, completed_at, created_at,
 *     template: { id, title, description, questions: [...] },
 *     answers: [{ id, question_id, answer_value }]
 *   }
 */
export default function Show({ checklist }) {
    const isDraft = checklist.status === 'draft';
    const questions = checklist.template?.questions ?? [];

    const [answers, setAnswers] = useState(() =>
        buildAnswersMap(questions, checklist.answers ?? [])
    );
    const [errors, setErrors] = useState({});
    const [savingDraft, setSavingDraft] = useState(false);
    const [submitting, setSubmitting] = useState(false);

    function handleAnswerChange(questionId, value) {
        setAnswers((prev) => ({ ...prev, [questionId]: value }));
        // Clear the error for this question when the user starts typing
        if (errors[questionId]) {
            setErrors((prev) => {
                const next = { ...prev };
                delete next[questionId];
                return next;
            });
        }
    }

    function handleSaveDraft() {
        setSavingDraft(true);
        router.post(
            `/checklists/${checklist.id}/save-draft`,
            { answers: serialiseAnswers(answers) },
            {
                preserveScroll: true,
                onFinish: () => setSavingDraft(false),
            }
        );
    }

    function handleSubmit() {
        setSubmitting(true);
        setErrors({});

        router.post(
            `/checklists/${checklist.id}/submit`,
            { answers: serialiseAnswers(answers) },
            {
                preserveScroll: true,
                onError: (responseErrors) => {
                    // Map unanswered question IDs to inline errors
                    const fieldErrors = {};

                    if (responseErrors.unanswered_questions) {
                        // The backend returns an array of { id, question_text }
                        const unanswered = responseErrors.unanswered_questions;
                        if (Array.isArray(unanswered)) {
                            unanswered.forEach((q) => {
                                fieldErrors[q.id] = 'This answer is required.';
                            });
                        }
                    }

                    // Also handle flat validation errors keyed by question id
                    Object.entries(responseErrors).forEach(([key, message]) => {
                        if (key !== 'unanswered_questions') {
                            fieldErrors[key] = Array.isArray(message) ? message[0] : message;
                        }
                    });

                    setErrors(fieldErrors);
                },
                onFinish: () => setSubmitting(false),
            }
        );
    }

    const hasErrors = Object.keys(errors).length > 0;

    return (
        <AppLayout>
            <Head title={checklist.template?.title ?? 'Checklist'} />

            <div className="max-w-3xl space-y-6">
                {/* Page header */}
                <div>
                    <nav className="flex items-center gap-2 text-sm text-gray-500 mb-1" aria-label="Breadcrumb">
                        <Link href="/checklists" className="hover:text-indigo-600 transition-colors">
                            My Checklists
                        </Link>
                        <svg className="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fillRule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clipRule="evenodd" />
                        </svg>
                        <span className="text-gray-900 font-medium truncate max-w-xs">
                            {checklist.template?.title ?? 'Checklist'}
                        </span>
                    </nav>

                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">
                                {checklist.template?.title ?? 'Checklist'}
                            </h1>
                            {checklist.template?.description && (
                                <p className="mt-1 text-sm text-gray-500">
                                    {checklist.template.description}
                                </p>
                            )}
                        </div>
                        <Badge status={checklist.status} />
                    </div>

                    {/* Completion date for completed instances */}
                    {!isDraft && checklist.completed_at && (
                        <p className="mt-2 text-sm text-gray-500">
                            Completed on{' '}
                            <span className="font-medium text-gray-700">
                                {new Date(checklist.completed_at).toLocaleDateString(undefined, {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                })}
                            </span>
                        </p>
                    )}
                </div>

                {/* Validation error summary */}
                {hasErrors && isDraft && (
                    <div
                        className="rounded-md bg-red-50 border border-red-200 p-4"
                        role="alert"
                        aria-live="polite"
                    >
                        <div className="flex gap-3">
                            <svg className="h-5 w-5 text-red-400 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clipRule="evenodd" />
                            </svg>
                            <div>
                                <h3 className="text-sm font-medium text-red-800">
                                    Please answer all required questions before submitting.
                                </h3>
                                <p className="mt-1 text-sm text-red-700">
                                    {Object.keys(errors).length} required{' '}
                                    {Object.keys(errors).length === 1 ? 'question is' : 'questions are'} unanswered.
                                </p>
                            </div>
                        </div>
                    </div>
                )}

                {/* Questions card */}
                <div className="bg-white rounded-lg border border-gray-200 shadow-sm divide-y divide-gray-100">
                    <div className="px-6 py-4">
                        <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            Questions
                            <span className="ml-1.5 text-xs font-normal text-gray-400 normal-case">
                                ({questions.length} {questions.length === 1 ? 'question' : 'questions'})
                            </span>
                        </h2>
                    </div>

                    {questions.length === 0 ? (
                        <div className="px-6 py-12 text-center text-gray-400 text-sm">
                            This template has no questions.
                        </div>
                    ) : (
                        <ol className="divide-y divide-gray-100" aria-label="Checklist questions">
                            {questions.map((question, index) => {
                                const questionError = errors[question.id];

                                return (
                                    <li key={question.id} className="px-6 py-5">
                                        <div className="space-y-3">
                                            {/* Question label */}
                                            <div className="flex items-start gap-2">
                                                <span className="flex-shrink-0 inline-flex items-center justify-center h-6 w-6 rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700 mt-0.5">
                                                    {index + 1}
                                                </span>
                                                <div className="flex-1">
                                                    <label
                                                        htmlFor={`answer-${question.id}`}
                                                        className={`block text-sm font-medium ${
                                                            questionError ? 'text-red-700' : 'text-gray-900'
                                                        }`}
                                                    >
                                                        {question.question_text}
                                                        {question.required && isDraft && (
                                                            <span className="ml-1 text-red-500" aria-label="required">
                                                                *
                                                            </span>
                                                        )}
                                                    </label>

                                                    {/* Answer type hint */}
                                                    <p className="mt-0.5 text-xs text-gray-400 capitalize">
                                                        {question.answer_type} answer
                                                        {question.required ? ' · required' : ' · optional'}
                                                    </p>
                                                </div>
                                            </div>

                                            {/* Answer input */}
                                            <div className="ml-8">
                                                <AnswerInput
                                                    question={question}
                                                    value={answers[question.id]}
                                                    onChange={(value) => handleAnswerChange(question.id, value)}
                                                    error={questionError}
                                                    readOnly={!isDraft}
                                                />
                                            </div>
                                        </div>
                                    </li>
                                );
                            })}
                        </ol>
                    )}
                </div>

                {/* Action buttons — only shown for draft instances */}
                {isDraft && (
                    <div className="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                        <Link
                            href="/checklists"
                            className="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
                        >
                            Back to list
                        </Link>

                        <div className="flex items-center gap-3">
                            <button
                                type="button"
                                onClick={handleSaveDraft}
                                disabled={savingDraft || submitting}
                                className="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                {savingDraft ? 'Saving…' : 'Save Draft'}
                            </button>

                            <button
                                type="button"
                                onClick={handleSubmit}
                                disabled={savingDraft || submitting}
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                {submitting ? 'Submitting…' : 'Submit'}
                            </button>
                        </div>
                    </div>
                )}

                {/* Back link for completed instances */}
                {!isDraft && (
                    <div className="pt-2">
                        <Link
                            href="/checklists"
                            className="inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
                        >
                            <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fillRule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clipRule="evenodd" />
                            </svg>
                            Back to My Checklists
                        </Link>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
