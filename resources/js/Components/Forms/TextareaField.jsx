export default function TextareaField({
    label,
    name,
    value,
    onChange,
    error,
    rows = 4,
    ...rest
}) {
    const textareaId = `field-${name}`;
    const errorId = `error-${name}`;

    return (
        <div>
            <label
                htmlFor={textareaId}
                className="block text-sm font-medium text-gray-700 mb-1"
            >
                {label}
            </label>
            <textarea
                id={textareaId}
                name={name}
                value={value}
                onChange={onChange}
                rows={rows}
                aria-describedby={error ? errorId : undefined}
                aria-invalid={error ? 'true' : undefined}
                className={`block w-full rounded-md border px-3 py-2 text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-y ${
                    error
                        ? 'border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 text-gray-900'
                }`}
                {...rest}
            />
            {error && (
                <p id={errorId} className="mt-1 text-sm text-red-600" role="alert">
                    {error}
                </p>
            )}
        </div>
    );
}
