import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { dateTime, money, transactionType } from '@/lib/format';
import { Head, Link, router, useForm } from '@inertiajs/react';

export default function Index({ transactions, filters }: any) {
    const filterForm = useForm({ search: filters.search || '', type: filters.type || '', status: filters.status || '', from: filters.from || '', to: filters.to || '' });
    const apply = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('transactions.index'), filterForm.data, { preserveState: true });
    };
    const cancel = (id: number) => {
        const reason = window.prompt('Motivo de anulacion');
        if (reason) router.post(route('transactions.cancel', id), { reason }, { preserveScroll: true });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Movimientos</h2>}>
            <Head title="Movimientos" />
            <div className="mx-auto max-w-5xl px-4 py-6">
                <div className="flex flex-wrap gap-2">
                    <Link href={route('transactions.create')} className="rounded-lg bg-teal-700 px-4 py-2 font-medium text-white">Nuevo movimiento</Link>
                    <Link href={route('transactions.import')} className="rounded-lg bg-white px-4 py-2 font-medium text-gray-700 shadow-sm">Importar CSV</Link>
                </div>
                <form onSubmit={apply} className="mt-4 grid gap-2 rounded-lg bg-white p-3 shadow-sm sm:grid-cols-5">
                    <input placeholder="Buscar" value={filterForm.data.search} onChange={(e) => filterForm.setData('search', e.target.value)} className="rounded-lg border-gray-300" />
                    <select value={filterForm.data.type} onChange={(e) => filterForm.setData('type', e.target.value)} className="rounded-lg border-gray-300"><option value="">Todos los tipos</option><option value="income">Ingreso</option><option value="expense">Egreso</option><option value="transfer">Transferencia</option><option value="adjustment">Ajuste</option><option value="loan_received">Prestamo recibido</option><option value="loan_given">Prestamo otorgado</option><option value="loan_payment">Pago</option><option value="loan_collection">Cobro</option></select>
                    <select value={filterForm.data.status} onChange={(e) => filterForm.setData('status', e.target.value)} className="rounded-lg border-gray-300"><option value="">Todos los estados</option><option value="confirmed">Confirmado</option><option value="pending">Pendiente</option><option value="cancelled">Anulado</option></select>
                    <input type="date" value={filterForm.data.from} onChange={(e) => filterForm.setData('from', e.target.value)} className="rounded-lg border-gray-300" />
                    <button className="rounded-lg bg-slate-800 px-4 py-2 font-medium text-white">Filtrar</button>
                    <input type="date" value={filterForm.data.to} onChange={(e) => filterForm.setData('to', e.target.value)} className="rounded-lg border-gray-300 sm:col-start-4" />
                </form>
                <div className="mt-4 divide-y rounded-lg bg-white shadow-sm">
                    {transactions.data.map((tx: any) => (
                        <div key={tx.id} className="flex flex-wrap items-start justify-between gap-4 p-4">
                            <div><div className="font-medium">{tx.description || transactionType(tx.type)} {tx.status === 'cancelled' && <span className="text-rose-700">(anulado)</span>}</div><div className="text-sm text-gray-500">{dateTime(tx.date)} · {tx.wallet?.name} · {tx.category?.name || tx.destination_wallet?.name || ''}</div></div>
                            <div className="text-right">
                                <strong>{money(tx.display_amount ?? tx.amount, tx.display_currency ?? tx.wallet?.currency)}</strong>
                                {tx.original_currency && <div className="text-xs text-gray-500">Registrado: {money(tx.original_amount ?? tx.amount, tx.original_currency)}</div>}
                                {tx.destination_amount && <div className="text-xs text-gray-500">Destino {money(tx.destination_amount, tx.destination_wallet?.currency)}</div>}
                                <div className="mt-2 flex flex-wrap justify-end gap-2 text-sm">
                                    {tx.attachments?.map((file: any) => <a key={file.id} href={route('transactions.attachments.show', file.id)} className="text-teal-700">Adjunto</a>)}
                                    <Link href={route('transactions.edit', tx.id)} className="text-slate-700">Editar</Link>
                                    <button onClick={() => router.post(route('transactions.duplicate', tx.id), {}, { preserveScroll: true })} className="text-slate-700">Duplicar</button>
                                    {tx.status !== 'cancelled' && <button onClick={() => cancel(tx.id)} className="text-rose-700">Anular</button>}
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
