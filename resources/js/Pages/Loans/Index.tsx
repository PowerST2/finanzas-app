import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { loanKind, money } from '@/lib/format';
import { Head, Link } from '@inertiajs/react';

export default function Index({ loans }: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Prestamos</h2>}>
            <Head title="Prestamos" />
            <div className="mx-auto max-w-4xl px-4 py-6">
                <Link href={route('loans.create')} className="inline-block rounded-lg bg-teal-700 px-4 py-2 font-medium text-white">Registrar prestamo</Link>
                <div className="mt-4 divide-y rounded-lg bg-white shadow-sm">
                    {loans.map((loan: any) => (
                        <Link key={loan.id} href={route('loans.show', loan.id)} className="flex justify-between p-4">
                            <div><div className="font-medium">{loan.name}</div><div className="text-sm text-gray-500">{loanKind(loan.kind)} · {loan.lender_name || 'Sin persona'} · {loan.status === 'paid' ? 'pagado' : 'activo'}</div></div>
                            <strong>{money(loan.current_balance)}</strong>
                        </Link>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
