export default function InputField({
    label,
    name,
    value,
    onChange,
    error,
    type = 'text',
    ...rest
}) {
    const inputId = `field-${name}`;
    const errorId = `error-${name}`;

    return (
        <div>
            <label
                htmlFor={inputId}
                className="block text-sm font-medium text-gray-700 mb-1"
            >
                {label}
            </label>
            <input
                id={inputId}
                name={name}
                type={type}
                value={value}
                onChange={onChange}
                aria-describedby={error ? errorId : undefined}
                aria-invalid={error ? 'true' : undefined}
                className={`block w-full rounded-md border px-3 py-2 text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors ${
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
