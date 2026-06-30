import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { loanKind, money } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ loans }: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Prestamos</h2>}>
            <Head title="Prestamos" />
            <div className="mx-auto max-w-4xl px-4 py-6">
                <Link href={route('loans.create')} className="inline-block rounded-lg bg-teal-700 px-4 py-2 font-medium text-white">Registrar prestamo</Link>
                <div className="mt-4 divide-y rounded-lg bg-white shadow-sm">
                    {loans.map((loan: any) => (
                        <div key={loan.id} className="flex items-center justify-between gap-3 p-4">
                            <Link href={route('loans.show', loan.id)} className="min-w-0 flex-1">
                                <div className="font-medium">{loan.name}</div>
                                <div className="text-sm text-gray-500">{loanKind(loan.kind)} · {loan.lender_name || 'Sin persona'} · {statusLabel(loan.status)}</div>
                            </Link>
                            <strong>{money(loan.current_balance)}</strong>
                            <div className="flex gap-2 text-sm">
                                {loan.status !== 'paid' && <button onClick={() => router.post(route(loan.status === 'suspended' ? 'loans.resume' : 'loans.suspend', loan.id), {}, { preserveScroll: true })} className="text-slate-700">{loan.status === 'suspended' ? 'Reactivar' : 'Suspender'}</button>}
                                <button onClick={() => confirm('Eliminar este prestamo tambien eliminara sus movimientos. Continuar?') && router.delete(route('loans.destroy', loan.id))} className="text-rose-700">Eliminar</button>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function statusLabel(status: string) {
    return status === 'paid' ? 'pagado' : status === 'suspended' ? 'suspendido' : 'activo';
}
