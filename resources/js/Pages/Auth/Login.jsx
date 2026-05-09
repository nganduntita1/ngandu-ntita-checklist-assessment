import { useForm, Head } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import InputField from '../../Components/Forms/InputField';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/login');
    }

    return (
        <GuestLayout>
            <Head title="Login" />

            <form onSubmit={handleSubmit} noValidate className="space-y-6">
                <InputField
                    label="Email address"
                    name="email"
                    type="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                    autoComplete="email"
                    autoFocus
                    required
                />

                <InputField
                    label="Password"
                    name="password"
                    type="password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                    autoComplete="current-password"
                    required
                />

                <div>
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {processing ? 'Signing in…' : 'Sign in'}
                    </button>
                </div>
            </form>
        </GuestLayout>
    );
}
