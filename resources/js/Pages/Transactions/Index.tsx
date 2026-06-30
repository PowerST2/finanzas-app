import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { dateTime, money, transactionType } from '@/lib/format';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowDownLeft, ArrowUpRight, Copy, FileText, Pencil, Plus, Search, Trash2, XCircle } from 'lucide-react';

export default function Index({ transactions, filters, wallets }: any) {
    const filterForm = useForm({ search: filters.search || '', type: filters.type || '', status: filters.status || '', from: filters.from || '', to: filters.to || '', scope: filters.scope || 'real', wallet_id: filters.wallet_id || '' });
    const apply = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('transactions.index'), filterForm.data, { preserveState: true });
    };
    const cancel = (id: number) => {
        const reason = window.prompt('Motivo de anulacion');
        if (reason) router.post(route('transactions.cancel', id), { reason }, { preserveScroll: true });
    };

    return (
        <AuthenticatedLayout header={<h2>Movimientos</h2>}>
            <Head title="Movimientos" />
            <div className="space-y-5">
                <div className="flex flex-wrap gap-3">
                    <Link href={route('transactions.create')} className="app-action inline-flex items-center gap-2 bg-slate-950 px-4 py-3"><Plus className="h-5 w-5" /> Nuevo movimiento</Link>
                    <Link href={route('transactions.import')} className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 font-bold text-slate-700 shadow-sm"><FileText className="h-5 w-5" /> Importar CSV</Link>
                </div>

                <form onSubmit={apply} className="app-section grid gap-3 p-4 md:grid-cols-6">
                    <label className="relative md:col-span-2">
                        <Search className="pointer-events-none absolute left-3 top-3 h-5 w-5 text-slate-400" />
                        <input placeholder="Buscar movimiento" value={filterForm.data.search} onChange={(e) => filterForm.setData('search', e.target.value)} className="w-full border-gray-300 pl-10" />
                    </label>
                    <select value={filterForm.data.scope} onChange={(e) => filterForm.setData({ ...filterForm.data, scope: e.target.value, wallet_id: '' })} className="border-gray-300"><option value="real">Dinero real</option><option value="all">Todo</option></select>
                    <select value={filterForm.data.wallet_id} onChange={(e) => filterForm.setData('wallet_id', e.target.value)} className="border-gray-300"><option value="">Todas las billeteras</option>{wallets.map((wallet: any) => <option key={wallet.id} value={wallet.id}>{wallet.name}{wallet.type === 'credit_card' ? ' · tarjeta' : ''}</option>)}</select>
                    <select value={filterForm.data.type} onChange={(e) => filterForm.setData('type', e.target.value)} className="border-gray-300"><option value="">Todos los tipos</option><option value="income">Ingreso</option><option value="expense">Egreso</option><option value="transfer">Transferencia</option><option value="adjustment">Ajuste</option><option value="loan_received">Prestamo recibido</option><option value="loan_given">Prestamo otorgado</option><option value="loan_payment">Pago</option><option value="loan_collection">Cobro</option></select>
                    <select value={filterForm.data.status} onChange={(e) => filterForm.setData('status', e.target.value)} className="border-gray-300"><option value="">Todos los estados</option><option value="confirmed">Confirmado</option><option value="pending">Pendiente</option><option value="cancelled">Anulado</option></select>
                    <input type="date" value={filterForm.data.from} onChange={(e) => filterForm.setData('from', e.target.value)} className="border-gray-300" />
                    <button className="rounded-2xl bg-teal-600 px-4 py-2 font-bold text-white">Filtrar</button>
                    <input type="date" value={filterForm.data.to} onChange={(e) => filterForm.setData('to', e.target.value)} className="border-gray-300 md:col-start-5" />
                </form>

                <div className="grid gap-3">
                    {transactions.data.length ? transactions.data.map((tx: any) => (
                        <article key={tx.id} className="app-section flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex min-w-0 items-center gap-4">
                                <div className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ${tx.type?.includes('income') || tx.type === 'loan_received' || tx.type === 'loan_collection' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}`}>
                                    {tx.type?.includes('income') || tx.type === 'loan_received' || tx.type === 'loan_collection' ? <ArrowDownLeft className="h-6 w-6" /> : <ArrowUpRight className="h-6 w-6" />}
                                </div>
                                <div className="min-w-0">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h3 className="truncate font-black text-slate-950">{tx.description || transactionType(tx.type)}</h3>
                                        <StatusBadge status={tx.status} />
                                    </div>
                                    <div className="mt-1 text-sm font-semibold text-slate-500">{dateTime(tx.date)} · {tx.wallet?.name} · {tx.category?.name || tx.destination_wallet?.name || 'Sin categoria'}</div>
                                </div>
                            </div>

                            <div className="flex flex-wrap items-center justify-between gap-4 sm:justify-end">
                                <div className="text-left sm:text-right">
                                    <div className="text-xl font-black text-slate-950">{money(tx.display_amount ?? tx.amount, tx.display_currency ?? tx.wallet?.currency)}</div>
                                    {tx.original_currency && <div className="text-xs font-semibold text-slate-500">Registrado: {money(tx.original_amount ?? tx.amount, tx.original_currency)}</div>}
                                    {tx.destination_amount && <div className="text-xs font-semibold text-slate-500">Destino {money(tx.destination_amount, tx.destination_wallet?.currency)}</div>}
                                </div>
                                <div className="flex gap-2">
                                    {tx.attachments?.map((file: any) => <a key={file.id} href={route('transactions.attachments.show', file.id)} className="rounded-xl bg-slate-100 p-2 text-slate-700"><FileText className="h-4 w-4" /></a>)}
                                    <Link href={route('transactions.edit', tx.id)} className="rounded-xl bg-slate-100 p-2 text-slate-700"><Pencil className="h-4 w-4" /></Link>
                                    <button onClick={() => router.post(route('transactions.duplicate', tx.id), {}, { preserveScroll: true })} className="rounded-xl bg-slate-100 p-2 text-slate-700"><Copy className="h-4 w-4" /></button>
                                    {tx.status !== 'cancelled' && <button onClick={() => cancel(tx.id)} className="rounded-xl bg-rose-50 p-2 text-rose-700"><XCircle className="h-4 w-4" /></button>}
                                    <button onClick={() => confirm('Eliminar este movimiento?') && router.delete(route('transactions.destroy', tx.id), { preserveScroll: true })} className="rounded-xl bg-rose-50 p-2 text-rose-700"><Trash2 className="h-4 w-4" /></button>
                                </div>
                            </div>
                        </article>
                    )) : <div className="app-section p-8 text-center font-semibold text-slate-500">No hay movimientos para mostrar.</div>}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function StatusBadge({ status }: { status: string }) {
    const styles: Record<string, string> = {
        confirmed: 'bg-emerald-50 text-emerald-700',
        pending: 'bg-amber-50 text-amber-700',
        cancelled: 'bg-rose-50 text-rose-700',
    };

    return <span className={`rounded-full px-3 py-1 text-xs font-black ${styles[status] || 'bg-slate-100 text-slate-600'}`}>{status === 'confirmed' ? 'Confirmado' : status === 'pending' ? 'Pendiente' : 'Anulado'}</span>;
}
