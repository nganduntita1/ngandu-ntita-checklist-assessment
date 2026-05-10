import { Link, usePage } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import Badge from '../Components/UI/Badge';

function StatCard({ label, value, colorClass = 'text-indigo-600' }) {
    return (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col gap-2">
            <span className="text-sm font-medium text-gray-500">{label}</span>
            <span className={`text-4xl font-bold ${colorClass}`}>{value}</span>
        </div>
    );
}

function AdminDashboard({ templateCount, recentInstances }) {
    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
                <p className="mt-1 text-sm text-gray-500">Overview of templates and recent checklist activity.</p>
            </div>

            {/* Stat cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <StatCard label="Total Templates" value={templateCount} colorClass="text-indigo-600" />
            </div>

            {/* Recent instances */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200">
                <div className="px-6 py-4 border-b border-gray-200">
                    <h2 className="text-lg font-semibold text-gray-900">Recent Checklist Instances</h2>
                    <p className="text-sm text-gray-500 mt-0.5">Last 10 submitted or in-progress checklists.</p>
                </div>

                {recentInstances.length === 0 ? (
                    <div className="px-6 py-12 text-center text-gray-400 text-sm">
                        No checklist instances yet.
                    </div>
                ) : (
                    <>
                        {/* Desktop table */}
                        <div className="hidden sm:block overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Auditor
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Template
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Completed
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {recentInstances.map((instance) => (
                                        <tr key={instance.id} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {instance.auditor_name ?? '—'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {instance.template_title ?? '—'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <Badge status={instance.status} />
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {instance.completed_at
                                                    ? new Date(instance.completed_at).toLocaleDateString(undefined, {
                                                          year: 'numeric',
                                                          month: 'short',
                                                          day: 'numeric',
                                                      })
                                                    : '—'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Mobile card stack */}
                        <div className="sm:hidden divide-y divide-gray-200">
                            {recentInstances.map((instance) => (
                                <div key={instance.id} className="px-4 py-4 space-y-2">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium text-gray-900">
                                            {instance.auditor_name ?? '—'}
                                        </span>
                                        <Badge status={instance.status} />
                                    </div>
                                    <p className="text-sm text-gray-600">{instance.template_title ?? '—'}</p>
                                    {instance.completed_at && (
                                        <p className="text-xs text-gray-400">
                                            Completed:{' '}
                                            {new Date(instance.completed_at).toLocaleDateString(undefined, {
                                                year: 'numeric',
                                                month: 'short',
                                                day: 'numeric',
                                            })}
                                        </p>
                                    )}
                                </div>
                            ))}
                        </div>
                    </>
                )}
            </div>
        </div>
    );
}

function AuditorDashboard({ draftCount, completedCount }) {
    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Auditor Dashboard</h1>
                <p className="mt-1 text-sm text-gray-500">Your checklist progress at a glance.</p>
            </div>

            {/* Stat cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <StatCard label="Drafts in Progress" value={draftCount} colorClass="text-amber-600" />
                <StatCard label="Completed Checklists" value={completedCount} colorClass="text-green-600" />
            </div>

            {/* Quick-start CTA */}
            <div className="bg-indigo-50 border border-indigo-200 rounded-lg p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 className="text-base font-semibold text-indigo-900">Ready to start a new checklist?</h2>
                    <p className="text-sm text-indigo-700 mt-1">
                        Browse available templates and begin a new compliance checklist.
                    </p>
                </div>
                <Link
                    href="/checklists/start"
                    className="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors whitespace-nowrap"
                >
                    Start New Checklist
                </Link>
            </div>
        </div>
    );
}

export default function Dashboard({ templateCount, recentInstances, draftCount, completedCount }) {
    const { auth } = usePage().props;
    const isAdmin = auth?.user?.role === 'admin';

    return (
        <AppLayout>
            {isAdmin ? (
                <AdminDashboard
                    templateCount={templateCount ?? 0}
                    recentInstances={recentInstances ?? []}
                />
            ) : (
                <AuditorDashboard
                    draftCount={draftCount ?? 0}
                    completedCount={completedCount ?? 0}
                />
            )}
        </AppLayout>
    );
}
