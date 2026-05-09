export default function BooleanField({
    label,
    name,
    checked,
    onChange,
    error,
    ...rest
}) {
    const checkboxId = `field-${name}`;
    const errorId = `error-${name}`;

    return (
        <div>
            <div className="flex items-center gap-3">
                {/* Toggle switch */}
                <button
                    type="button"
                    role="switch"
                    id={checkboxId}
                    aria-checked={checked}
                    aria-describedby={error ? errorId : undefined}
                    onClick={() => onChange({ target: { name, checked: !checked, type: 'checkbox' } })}
                    className={`relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 ${
                        checked ? 'bg-indigo-600' : 'bg-gray-200'
                    }`}
                    {...rest}
                >
                    <span
                        aria-hidden="true"
                        className={`pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${
                            checked ? 'translate-x-5' : 'translate-x-0'
                        }`}
                    />
                </button>

                {/* Hidden checkbox for form compatibility */}
                <input
                    type="checkbox"
                    name={name}
                    checked={checked}
                    onChange={onChange}
                    className="sr-only"
                    tabIndex={-1}
                    aria-hidden="true"
                />

                <label
                    htmlFor={checkboxId}
                    className="text-sm font-medium text-gray-700 cursor-pointer select-none"
                >
                    {label}
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
