import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { dateTime, money, transactionType } from '@/lib/format';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ summary, wallets, alerts, recentTransactions, topExpenses, lastBackup }: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Panel</h2>}>
            <Head title="Panel" />
            <div className="mx-auto max-w-7xl space-y-4 px-4 py-6">
                <section className="grid gap-3 sm:grid-cols-4">
                    {[
                        [`Saldo total ${summary.currency || 'PEN'}`, summary.total],
                        ['Ingresos mes', summary.income],
                        ['Egresos mes', summary.expense],
                        ['Deuda por pagar', summary.debt],
                        ['Deuda por cobrar', summary.receivable],
                    ].map(([label, value]) => (
                        <div key={label as string} className="rounded-lg bg-white p-4 shadow-sm">
                            <div className="text-sm text-gray-500">{label}</div>
                            <div className="mt-1 text-2xl font-semibold text-gray-900">{money(value as number, summary.currency)}</div>
                        </div>
                    ))}
                </section>

                <section className="grid gap-2 sm:grid-cols-4">
                    <Link className="rounded-lg bg-teal-700 px-4 py-3 text-center font-medium text-white" href={route('transactions.create', { type: 'income' })}>Ingreso</Link>
                    <Link className="rounded-lg bg-rose-700 px-4 py-3 text-center font-medium text-white" href={route('transactions.create', { type: 'expense' })}>Egreso</Link>
                    <Link className="rounded-lg bg-slate-800 px-4 py-3 text-center font-medium text-white" href={route('transactions.create', { type: 'transfer' })}>Transferencia</Link>
                    <Link className="rounded-lg bg-amber-700 px-4 py-3 text-center font-medium text-white" href={route('loans.index')}>Prestamos</Link>
                </section>
                <div className="rounded-lg bg-white p-3 text-sm text-gray-600 shadow-sm">Ultimo backup local: {lastBackup ? lastBackup.split('/').pop() : 'sin backups'}</div>

                <section className="grid gap-4 lg:grid-cols-2">
                    <div className="rounded-lg bg-white p-4 shadow-sm">
                        <h3 className="font-semibold text-gray-900">Billeteras</h3>
                        <div className="mt-3 divide-y">
                            {wallets.map((wallet: any) => (
                                <div key={wallet.id} className="flex justify-between py-3">
                                    <span>{wallet.name}</span>
                                    <strong>{money(wallet.display_balance ?? wallet.current_balance_cache, wallet.display_currency ?? summary.currency)}</strong>
                                </div>
                            ))}
                        </div>
                    </div>
                    <div className="rounded-lg bg-white p-4 shadow-sm">
                        <h3 className="font-semibold text-gray-900">Alertas</h3>
                        <div className="mt-3 space-y-2">
                            {alerts.length ? alerts.map((alert: any) => (
                                <div key={alert.id} className="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm">{alert.title}</div>
                            )) : <p className="text-sm text-gray-500">Sin alertas activas.</p>}
                        </div>
                    </div>
                </section>
                <section className="rounded-lg bg-white p-4 shadow-sm">
                    <h3 className="font-semibold text-gray-900">Gastos principales</h3>
                    <div className="mt-3 space-y-3">
                        {topExpenses.map((row: any) => {
                            const max = Math.max(...topExpenses.map((x: any) => Number(x.amount)), 1);
                            return <div key={row.name} className="text-sm"><div className="mb-1 flex justify-between"><span>{row.name}</span><strong>{money(row.amount, summary.currency)}</strong></div><div className="h-2 rounded-full bg-gray-100"><div className="h-2 rounded-full bg-rose-700" style={{ width: `${(Number(row.amount) / max) * 100}%` }} /></div></div>;
                        })}
                    </div>
                </section>

                <section className="rounded-lg bg-white p-4 shadow-sm">
                    <h3 className="font-semibold text-gray-900">Ultimos movimientos</h3>
                    <div className="mt-3 divide-y">
                        {recentTransactions.map((tx: any) => (
                            <div key={tx.id} className="flex justify-between gap-3 py-3 text-sm">
                                <div>
                                    <div className="font-medium">{tx.description || transactionType(tx.type)}</div>
                                    <div className="text-gray-500">{tx.wallet?.name} · {dateTime(tx.date)}</div>
                                </div>
                                <div className="font-semibold">{money(tx.display_amount ?? tx.amount, tx.display_currency ?? summary.currency)}</div>
                            </div>
                        ))}
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
