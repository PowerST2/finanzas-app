import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { money, month } from '@/lib/format';
import { Head, Link } from '@inertiajs/react';

export default function Index({ budgets }: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Presupuestos</h2>}>
            <Head title="Presupuestos" />
            <div className="mx-auto max-w-4xl px-4 py-6">
                <Link href={route('budgets.show', month())} className="inline-block rounded-lg bg-teal-700 px-4 py-2 font-medium text-white">Presupuesto del mes</Link>
                <div className="mt-4 divide-y rounded-lg bg-white shadow-sm">
                    {budgets.map((budget: any) => (
                        <Link key={budget.id} href={route('budgets.show', budget.month)} className="flex justify-between p-4"><span>{budget.month}</span><strong>{money(budget.total_limit)}</strong></Link>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
