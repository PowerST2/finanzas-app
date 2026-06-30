import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { dateTime, money, transactionType } from '@/lib/format';
import { Head, Link } from '@inertiajs/react';
import { ArrowDownLeft, ArrowUpRight, Bell, CreditCard, Landmark, Plus, TrendingDown, TrendingUp, WalletCards } from 'lucide-react';

export default function Dashboard({ summary, wallets, creditCards, alerts, recentTransactions, topExpenses, lastBackup }: any) {
    const currency = summary.currency || 'PEN';
    const maxExpense = Math.max(...topExpenses.map((x: any) => Number(x.amount)), 1);

    return (
        <AuthenticatedLayout header={<h2>Panel financiero</h2>}>
            <Head title="Panel" />
            <div className="space-y-6">
                <section className="grid gap-4 xl:grid-cols-[1.35fr_0.65fr]">
                    <div className="relative overflow-hidden rounded-[2rem] bg-slate-950 p-6 text-white shadow-2xl shadow-slate-900/20 sm:p-8">
                        <div className="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-l from-teal-400/25 to-transparent" />
                        <div className="relative z-10">
                            <div className="flex items-center gap-2 text-sm font-bold text-teal-200">
                                <WalletCards className="h-4 w-4" />
                                Dinero disponible real
                            </div>
                            <div className="mt-5 text-4xl font-black tracking-tight sm:text-6xl">{money(summary.total, currency)}</div>
                            <div className="mt-4 grid max-w-2xl gap-3 sm:grid-cols-2">
                                <MiniMetric icon={TrendingUp} label="Ingresos del mes" value={money(summary.income, currency)} tone="text-emerald-300" />
                                <MiniMetric icon={TrendingDown} label="Egresos del mes" value={money(summary.expense, currency)} tone="text-rose-300" />
                            </div>
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                        <SideMetric icon={Landmark} label="Deuda por pagar" value={money(summary.debt, currency)} tone="bg-amber-50 text-amber-700" />
                        <SideMetric icon={CreditCard} label="Por cobrar" value={money(summary.receivable, currency)} tone="bg-violet-50 text-violet-700" />
                    </div>
                </section>

                <section className="grid gap-3 sm:grid-cols-4">
                    <QuickAction href={route('transactions.create', { type: 'income' })} icon={ArrowDownLeft} label="Ingreso" color="bg-emerald-600" />
                    <QuickAction href={route('transactions.create', { type: 'expense' })} icon={ArrowUpRight} label="Egreso" color="bg-rose-600" />
                    <QuickAction href={route('transactions.create', { type: 'transfer' })} icon={CreditCard} label="Transferencia" color="bg-slate-950" />
                    <QuickAction href={route('loans.index')} icon={Plus} label="Prestamos" color="bg-amber-400 text-slate-950" />
                </section>

                <section className="grid gap-5 xl:grid-cols-[0.9fr_1.1fr]">
                    <div className="app-section p-5">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-black text-slate-950">Billeteras</h3>
                            <Link href={route('wallets.index')} className="text-sm font-bold text-teal-700">Ver todas</Link>
                        </div>
                        <div className="mt-4 space-y-3">
                            {wallets.map((wallet: any) => (
                                <div key={wallet.id} className="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                                    <div>
                                        <div className="font-bold text-slate-900">{wallet.name}</div>
                                        <div className="text-xs font-semibold text-slate-500">Moneda real {wallet.currency}</div>
                                    </div>
                                    <div className="text-right font-black text-slate-950">{money(wallet.display_balance ?? wallet.current_balance_cache, wallet.display_currency ?? currency)}</div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="app-section p-5">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-black text-slate-950">Gastos principales</h3>
                            <Link href={route('reports.monthly')} className="text-sm font-bold text-teal-700">Reportes</Link>
                        </div>
                        <div className="mt-5 space-y-4">
                            {topExpenses.length ? topExpenses.map((row: any) => (
                                <div key={row.name}>
                                    <div className="mb-2 flex justify-between text-sm">
                                        <span className="font-bold text-slate-700">{row.name}</span>
                                        <span className="font-black text-slate-950">{money(row.amount, currency)}</span>
                                    </div>
                                    <div className="h-3 overflow-hidden rounded-full bg-slate-100">
                                        <div className="h-full rounded-full bg-gradient-to-r from-rose-500 to-amber-400" style={{ width: `${(Number(row.amount) / maxExpense) * 100}%` }} />
                                    </div>
                                </div>
                            )) : <EmptyLine text="Aun no hay gastos este mes." />}
                        </div>
                    </div>
                </section>

                <section className="app-section p-5">
                    <div className="flex items-center justify-between">
                        <h3 className="text-lg font-black text-slate-950">Tarjetas de credito</h3>
                        <Link href={route('credit-cards.index')} className="text-sm font-bold text-teal-700">Ver tarjetas</Link>
                    </div>
                    <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        {creditCards.length ? creditCards.map((card: any) => (
                            <div key={card.id} className="rounded-3xl bg-violet-50 p-4">
                                <div className="font-black text-slate-950">{card.name}</div>
                                <div className="mt-3 grid grid-cols-2 gap-2 text-sm">
                                    <div><span className="block font-semibold text-slate-500">Utilizado</span><strong>{money(card.used_amount, card.currency)}</strong></div>
                                    <div><span className="block font-semibold text-slate-500">Disponible</span><strong>{money(card.available_amount, card.currency)}</strong></div>
                                </div>
                            </div>
                        )) : <EmptyLine text="Sin tarjetas de credito." />}
                    </div>
                </section>

                <section className="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
                    <div className="app-section p-5">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-black text-slate-950">Actividad reciente</h3>
                            <Link href={route('transactions.index')} className="text-sm font-bold text-teal-700">Movimientos</Link>
                        </div>
                        <div className="mt-4 divide-y divide-slate-100">
                            {recentTransactions.length ? recentTransactions.map((tx: any) => (
                                <div key={tx.id} className="flex items-center justify-between gap-4 py-4">
                                    <div className="flex min-w-0 items-center gap-3">
                                        <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                            {tx.type?.includes('income') ? <ArrowDownLeft className="h-5 w-5" /> : <ArrowUpRight className="h-5 w-5" />}
                                        </div>
                                        <div className="min-w-0">
                                            <div className="truncate font-bold text-slate-950">{tx.description || transactionType(tx.type)}</div>
                                            <div className="truncate text-xs font-semibold text-slate-500">{tx.wallet?.name} · {dateTime(tx.date)}</div>
                                        </div>
                                    </div>
                                    <div className="shrink-0 font-black text-slate-950">{money(tx.display_amount ?? tx.amount, tx.display_currency ?? currency)}</div>
                                </div>
                            )) : <EmptyLine text="Sin movimientos recientes." />}
                        </div>
                    </div>

                    <div className="app-section p-5">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-black text-slate-950">Alertas</h3>
                            <Bell className="h-5 w-5 text-amber-500" />
                        </div>
                        <div className="mt-4 space-y-3">
                            {alerts.length ? alerts.map((alert: any) => (
                                <div key={alert.id} className="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-950">{alert.title}</div>
                            )) : <EmptyLine text="Sin alertas activas." />}
                        </div>
                        <div className="mt-4 rounded-2xl bg-slate-50 p-4 text-xs font-semibold text-slate-500">
                            Ultimo backup local: {lastBackup ? lastBackup.split('/').pop() : 'sin backups'}
                        </div>
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}

function MiniMetric({ icon: Icon, label, value, tone }: any) {
    return <div className="rounded-3xl border border-white/10 bg-white/8 p-4">
        <div className={`flex items-center gap-2 text-xs font-bold ${tone}`}><Icon className="h-4 w-4" />{label}</div>
        <div className="mt-2 text-xl font-black">{value}</div>
    </div>;
}

function SideMetric({ icon: Icon, label, value, tone }: any) {
    return <div className="app-section p-5">
        <div className={`inline-flex rounded-2xl p-3 ${tone}`}><Icon className="h-6 w-6" /></div>
        <div className="mt-4 text-sm font-bold text-slate-500">{label}</div>
        <div className="mt-1 text-2xl font-black text-slate-950">{value}</div>
    </div>;
}

function QuickAction({ href, icon: Icon, label, color }: any) {
    return <Link href={href} className={`app-action flex items-center justify-center gap-2 px-4 py-4 ${color}`}>
        <Icon className="h-5 w-5" />
        {label}
    </Link>;
}

function EmptyLine({ text }: { text: string }) {
    return <div className="rounded-2xl bg-slate-50 p-4 text-sm font-semibold text-slate-500">{text}</div>;
}
