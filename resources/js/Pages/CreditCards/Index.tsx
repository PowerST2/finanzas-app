import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { money } from '@/lib/format';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { CreditCard, Plus, RotateCcw, Wallet } from 'lucide-react';

export default function Index({ cards, wallets }: any) {
    return (
        <AuthenticatedLayout header={<h2>Tarjetas de credito</h2>}>
            <Head title="Tarjetas de credito" />
            <div className="space-y-5">
                <div className="flex flex-wrap gap-3">
                    <Link href={route('wallets.create')} className="app-action inline-flex items-center gap-2 bg-slate-950 px-4 py-3">
                        <Plus className="h-5 w-5" /> Nueva tarjeta
                    </Link>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    {cards.length ? cards.map((card: any) => {
                        const percent = Math.min(100, (Number(card.used_amount) / Math.max(Number(card.opening_balance), 1)) * 100);

                        return (
                            <article key={card.id} className="app-section overflow-hidden p-5">
                                <div className="flex items-start justify-between gap-4">
                                    <div>
                                        <div className="inline-flex rounded-2xl bg-violet-50 p-3 text-violet-700"><CreditCard className="h-6 w-6" /></div>
                                        <h3 className="mt-4 text-xl font-black text-slate-950">{card.name}</h3>
                                        <p className="text-sm font-semibold text-slate-500">{card.currency} · {card.is_active ? 'Activa' : 'Suspendida'}</p>
                                    </div>
                                    <Link href={route('wallets.edit', card.id)} className="rounded-2xl bg-slate-100 px-3 py-2 text-sm font-bold text-slate-700">Editar</Link>
                                </div>

                                <div className="mt-5 grid gap-3 sm:grid-cols-3">
                                    <Metric label="Linea" value={money(card.opening_balance, card.currency)} />
                                    <Metric label="Deuda" value={money(card.pending_amount, card.currency)} />
                                    <Metric label="Disponible" value={money(card.available_amount, card.currency)} />
                                </div>

                                <div className="mt-4 grid gap-3 sm:grid-cols-4">
                                    <Metric label="Inicio" value={card.cycle_start} />
                                    <Metric label="Cierre" value={card.cycle_close} />
                                    <Metric label="Pago maximo" value={card.payment_due} />
                                    <Metric label="Reinicio" value={card.reset_date} />
                                </div>

                                <div className="mt-5 h-3 overflow-hidden rounded-full bg-slate-100">
                                    <div className="h-full rounded-full bg-gradient-to-r from-violet-500 to-rose-500" style={{ width: `${percent}%` }} />
                                </div>

                                <PaymentForm card={card} wallets={wallets} />

                                <div className="mt-5 flex flex-wrap items-center justify-between gap-3">
                                    <span className="text-sm font-semibold text-slate-500">{card.cycle_transactions_count} movimientos en este ciclo</span>
                                    <button onClick={() => router.post(route('credit-cards.reset', card.id), {}, { preserveScroll: true })} className="inline-flex items-center gap-2 rounded-2xl bg-violet-600 px-4 py-3 text-sm font-bold text-white">
                                        <RotateCcw className="h-4 w-4" /> Reiniciar ciclo
                                    </button>
                                </div>
                            </article>
                        );
                    }) : <div className="app-section p-8 text-center font-semibold text-slate-500">Aun no tienes tarjetas de credito.</div>}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Metric({ label, value }: any) {
    return <div className="rounded-2xl bg-slate-50 p-4">
        <div className="text-xs font-bold uppercase text-slate-500">{label}</div>
        <div className="mt-1 text-lg font-black text-slate-950">{value}</div>
    </div>;
}

function PaymentForm({ card, wallets }: any) {
    const form = useForm({ wallet_id: wallets[0]?.id || '', amount: card.pending_amount || '', date: nowLocal() });
    const selectedWallet = wallets.find((wallet: any) => Number(wallet.id) === Number(form.data.wallet_id));
    const sourceBalance = Number(selectedWallet?.current_balance_cache || 0);
    const sourceBalanceInCardCurrency = selectedWallet ? convert(sourceBalance, selectedWallet, card) : 0;
    const amount = Number(form.data.amount || 0);
    const pending = Number(card.pending_amount || 0);
    const maxPay = Math.min(pending, sourceBalanceInCardCurrency);
    const cannotPay = !wallets.length || pending <= 0 || amount <= 0 || amount > maxPay || form.processing;

    return <form onSubmit={(e) => {
        e.preventDefault();
        if (cannotPay) return;
        form.post(route('credit-cards.pay', card.id), { preserveScroll: true, onSuccess: () => form.reset('amount') });
    }} className="mt-5 grid gap-3 rounded-2xl bg-slate-50 p-4 sm:grid-cols-[1fr_1fr_auto]">
        <label className="text-sm font-bold text-slate-700">Pagar desde
            <select className="mt-1 w-full rounded-xl border-slate-200" value={form.data.wallet_id} onChange={(e) => form.setData('wallet_id', e.target.value)}>
                {wallets.map((wallet: any) => <option key={wallet.id} value={wallet.id}>{wallet.name}</option>)}
            </select>
            {selectedWallet && <span className="mt-1 block text-xs font-semibold text-slate-500">Saldo: {money(sourceBalance, selectedWallet.currency)}</span>}
        </label>
        <label className="text-sm font-bold text-slate-700">Monto en {card.currency}
            <input className="mt-1 w-full rounded-xl border-slate-200" type="number" step="0.01" min="0.01" max={maxPay || undefined} value={form.data.amount} onChange={(e) => form.setData('amount', e.target.value)} disabled={pending <= 0 || maxPay <= 0} />
            {pending > 0 && <span className="mt-1 block text-xs font-semibold text-slate-500">Maximo: {money(maxPay, card.currency)}</span>}
            {form.errors.amount && <span className="mt-1 block text-xs text-rose-600">{form.errors.amount}</span>}
            {form.errors.wallet_id && <span className="mt-1 block text-xs text-rose-600">{form.errors.wallet_id}</span>}
        </label>
        <button disabled={cannotPay} className="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white disabled:opacity-50">
            <Wallet className="h-4 w-4" /> Pagar
        </button>
        {pending <= 0 && <p className="text-sm font-semibold text-slate-500 sm:col-span-3">No hay deuda pendiente para pagar.</p>}
        {pending > 0 && selectedWallet && amount > maxPay && <p className="text-sm font-semibold text-rose-600 sm:col-span-3">El monto supera lo disponible en la billetera seleccionada.</p>}
    </form>;
}

function convert(amount: number, from: any, to: any) {
    if (from.currency === to.currency) return amount;

    return Math.round(((amount * Number(from.exchange_rate_to_pen || 1)) / Math.max(Number(to.exchange_rate_to_pen || 1), 0.0001)) * 100) / 100;
}

function nowLocal() {
    const date = new Date();
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 16);
}
