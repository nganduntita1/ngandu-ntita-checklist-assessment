export default function GuestLayout({ children }) {
    return (
        <div className="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
            <div className="sm:mx-auto sm:w-full sm:max-w-md">
                <h1 className="text-center text-3xl font-bold text-indigo-600">
                    Compliance Checklist
                </h1>
                <p className="mt-2 text-center text-sm text-gray-500">
                    Sign in to your account
                </p>
            </div>

            <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div className="bg-white py-8 px-4 shadow-md rounded-lg sm:px-10">
                    {children}
                </div>
            </div>
        </div>
    );
}
