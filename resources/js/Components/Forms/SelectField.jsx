export default function SelectField({
    label,
    name,
    value,
    onChange,
    error,
    options = [],
    ...rest
}) {
    const selectId = `field-${name}`;
    const errorId = `error-${name}`;

    return (
        <div>
            <label
                htmlFor={selectId}
                className="block text-sm font-medium text-gray-700 mb-1"
            >
                {label}
            </label>
            <select
                id={selectId}
                name={name}
                value={value}
                onChange={onChange}
                aria-describedby={error ? errorId : undefined}
                aria-invalid={error ? 'true' : undefined}
                className={`block w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors bg-white ${
                    error
                        ? 'border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500'
                        : 'border-gray-300 text-gray-900'
                }`}
                {...rest}
            >
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
            {error && (
                <p id={errorId} className="mt-1 text-sm text-red-600" role="alert">
                    {error}
                </p>
            )}
        </div>
    );
}
