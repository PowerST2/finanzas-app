import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { money, nowLocal } from '@/lib/format';
import { Head, router, useForm } from '@inertiajs/react';
import { CircleDollarSign } from 'lucide-react';

export default function Index({ debts, wallets, currencies }: any) {
    const form = useForm({ name: '', total_amount: '', currency: 'PEN', due_date: '', notes: '' });

    return (
        <AuthenticatedLayout header={<h2>Deudas pendientes</h2>}>
            <Head title="Deudas pendientes" />
            <div className="grid gap-5 lg:grid-cols-[360px_1fr]">
                <form onSubmit={(e) => { e.preventDefault(); form.post(route('pending-debts.store'), { preserveScroll: true, onSuccess: () => form.reset() }); }} className="app-section space-y-4 p-5">
                    <h3 className="text-lg font-black text-slate-950">Nueva deuda</h3>
                    <Field label="Nombre" value={form.data.name} onChange={(v: string) => form.setData('name', v)} error={form.errors.name} />
                    <div className="grid grid-cols-2 gap-3">
                        <Field label="Monto" type="number" step="0.01" value={form.data.total_amount} onChange={(v: string) => form.setData('total_amount', v)} error={form.errors.total_amount} />
                        <Select label="Moneda" value={form.data.currency} onChange={(v: string) => form.setData('currency', v)} options={currencies.map((x: any) => [x.code, x.code])} />
                    </div>
                    <Field label="Fecha limite" type="date" value={form.data.due_date} onChange={(v: string) => form.setData('due_date', v)} error={form.errors.due_date} />
                    <Field label="Notas" value={form.data.notes} onChange={(v: string) => form.setData('notes', v)} error={form.errors.notes} />
                    <button className="w-full rounded-2xl bg-slate-950 px-4 py-3 font-black text-white">Guardar deuda</button>
                </form>

                <div className="grid gap-4">
                    {debts.length ? debts.map((debt: any) => <DebtCard key={debt.id} debt={debt} wallets={wallets} />) : (
                        <div className="app-section p-8 text-center font-semibold text-slate-500">No tienes deudas pendientes.</div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function DebtCard({ debt, wallets }: any) {
    const form = useForm({ wallet_id: wallets[0]?.id || '', amount: '', paid_at: nowLocal(), notes: '' });
    const selectedWallet = wallets.find((wallet: any) => Number(wallet.id) === Number(form.data.wallet_id));
    const walletBalance = Number(selectedWallet?.current_balance_cache || 0);
    const walletBalanceInDebtCurrency = selectedWallet ? convert(walletBalance, selectedWallet, debt) : 0;
    const pending = Number(debt.current_balance || 0);
    const amount = Number(form.data.amount || 0);
    const maxPay = Math.min(pending, walletBalanceInDebtCurrency);
    const disabled = !wallets.length || amount <= 0 || amount > maxPay || form.processing;
    const paid = Number(debt.total_amount) - pending;
    const percent = Math.min(100, Math.round((paid / Math.max(Number(debt.total_amount), 1)) * 100));

    return <article className="app-section p-5">
        <div className="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div className="inline-flex rounded-2xl bg-amber-50 p-3 text-amber-700"><CircleDollarSign className="h-6 w-6" /></div>
                <h3 className="mt-3 text-xl font-black text-slate-950">{debt.name}</h3>
                <p className="text-sm font-semibold text-slate-500">Vence: {debt.due_date || 'sin fecha'}</p>
                <p className="text-sm font-semibold text-slate-500">Estado: {statusLabel(debt.status)}</p>
            </div>
            <div className="text-right">
                <div className="text-sm font-bold uppercase text-slate-500">Pendiente</div>
                <div className="text-2xl font-black text-slate-950">{money(debt.current_balance, debt.currency)}</div>
            </div>
        </div>
        <div className="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
            <div className="h-full rounded-full bg-gradient-to-r from-amber-400 to-emerald-500" style={{ width: `${percent}%` }} />
        </div>

        <div className="mt-4 flex gap-3 text-sm font-black">
            {debt.status !== 'paid' && <button onClick={() => router.post(route(debt.status === 'suspended' ? 'pending-debts.resume' : 'pending-debts.suspend', debt.id), {}, { preserveScroll: true })} className="text-slate-700">{debt.status === 'suspended' ? 'Reactivar' : 'Suspender'}</button>}
            <button onClick={() => confirm('Eliminar esta deuda tambien eliminara sus pagos. Continuar?') && router.delete(route('pending-debts.destroy', debt.id), { preserveScroll: true })} className="text-rose-700">Eliminar</button>
        </div>

        {debt.status === 'active' && <form onSubmit={(e) => {
            e.preventDefault();
            if (disabled) return;
            form.post(route('pending-debts.pay', debt.id), { preserveScroll: true, onSuccess: () => form.reset('amount', 'notes') });
        }} className="mt-4 grid gap-3 rounded-2xl bg-slate-50 p-4 sm:grid-cols-[1fr_1fr_auto]">
            <label className="text-sm font-bold text-slate-700">Pagar desde
                <select className="mt-1 w-full rounded-xl border-slate-200" value={form.data.wallet_id} onChange={(e) => form.setData('wallet_id', e.target.value)}>
                    {wallets.map((wallet: any) => <option key={wallet.id} value={wallet.id}>{wallet.name}</option>)}
                </select>
                {selectedWallet && <span className="mt-1 block text-xs font-semibold text-slate-500">Saldo: {money(walletBalance, selectedWallet.currency)}</span>}
            </label>
            <label className="text-sm font-bold text-slate-700">Monto en {debt.currency}
                <input className="mt-1 w-full rounded-xl border-slate-200" type="number" step="0.01" min="0.01" max={maxPay || undefined} value={form.data.amount} onChange={(e) => form.setData('amount', e.target.value)} />
                <span className="mt-1 block text-xs font-semibold text-slate-500">Maximo: {money(maxPay, debt.currency)}</span>
                <InputError message={form.errors.amount || form.errors.wallet_id} />
            </label>
            <button disabled={disabled} className="rounded-2xl bg-slate-950 px-5 py-3 font-black text-white disabled:opacity-50">Pagar</button>
            {amount > maxPay && <p className="text-sm font-semibold text-rose-600 sm:col-span-3">El monto supera lo disponible en la billetera seleccionada.</p>}
        </form>}
    </article>;
}

function convert(amount: number, from: any, to: any) {
    if (from.currency === to.currency) return amount;
    return Math.round(((amount * Number(from.exchange_rate_to_pen || 1)) / Math.max(Number(to.exchange_rate_to_pen || 1), 0.0001)) * 100) / 100;
}

function Field({ label, error, onChange, ...props }: any) { return <label className="block text-sm font-bold text-slate-700">{label}<input {...props} onChange={(e) => onChange(e.target.value)} className="mt-1 w-full rounded-xl border-slate-200" /><InputError message={error} className="mt-1" /></label>; }
function Select({ label, options, value, onChange }: any) { return <label className="block text-sm font-bold text-slate-700">{label}<select value={value} onChange={(e) => onChange(e.target.value)} className="mt-1 w-full rounded-xl border-slate-200">{options.map((x: any) => <option key={x[0]} value={x[0]}>{x[1]}</option>)}</select></label>; }
function statusLabel(status: string) { return status === 'paid' ? 'pagada' : status === 'suspended' ? 'suspendida' : 'activa'; }
