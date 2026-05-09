const statusStyles = {
    draft: 'bg-amber-100 text-amber-800',
    completed: 'bg-green-100 text-green-800',
    active: 'bg-blue-100 text-blue-800',
    inactive: 'bg-gray-100 text-gray-600',
};

export default function Badge({ status }) {
    const classes = statusStyles[status] ?? 'bg-gray-100 text-gray-600';

    return (
        <span
            className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${classes}`}
        >
            {status}
        </span>
    );
}
