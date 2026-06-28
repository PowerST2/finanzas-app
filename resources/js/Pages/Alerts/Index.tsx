import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function Index({ alerts }: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Alertas</h2>}>
            <Head title="Alertas" />
            <div className="mx-auto max-w-3xl space-y-3 px-4 py-6">
                {alerts.map((alert: any) => (
                    <button key={alert.id} onClick={() => router.patch(route('alerts.read', alert.id))} className={`block w-full rounded-lg bg-white p-4 text-left shadow-sm ${alert.read_at ? 'opacity-50' : ''}`}>
                        <div className="font-medium">{alert.title}</div>
                        <div className="text-sm text-gray-500">{alert.message}</div>
                    </button>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
