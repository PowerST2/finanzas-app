import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { dateTime, loanKind, money, nowLocal } from '@/lib/format';
import { Head, useForm } from '@inertiajs/react';

export default function Show({ loan, wallets }: any) {
    const form = useForm({ wallet_id: wallets[0]?.id || '', amount: '', paid_at: nowLocal(), notes: '' });
    const paid = Number(loan.principal_amount) - Number(loan.current_balance);
    const percent = Math.min(100, Math.round((paid / Number(loan.principal_amount)) * 100));

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">{loan.name}</h2>}>
            <Head title={loan.name} />
            <div className="mx-auto grid max-w-5xl gap-4 px-4 py-6 lg:grid-cols-2">
                <section className="rounded-lg bg-white p-4 shadow-sm">
                    <div className="text-sm text-gray-500">Pendiente</div>
                    <div className="text-3xl font-semibold">{money(loan.current_balance)}</div>
                    <div className="mt-2 text-sm text-gray-500">{loanKind(loan.kind)} · Original {money(loan.principal_amount)} · Estado {loan.status === 'paid' ? 'pagado' : 'activo'}</div>
                    <div className="mt-4 h-2 rounded-full bg-gray-100"><div className="h-2 rounded-full bg-teal-700" style={{ width: `${percent}%` }} /></div>
                    <div className="mt-2 text-sm text-gray-500">{percent}% avanzado · Vence {loan.due_date || 'sin fecha'}</div>
                    {loan.notes && <p className="mt-4 rounded-lg bg-gray-50 p-3 text-sm text-gray-600">{loan.notes}</p>}
                </section>
                <form onSubmit={(e) => { e.preventDefault(); form.post(route('loans.pay', loan.id)); }} className="space-y-4 rounded-lg bg-white p-4 shadow-sm">
                    <h3 className="font-semibold">{loan.kind === 'lent' ? 'Registrar cobro' : 'Registrar pago'}</h3>
                    <Select label="Billetera" value={form.data.wallet_id} onChange={(v: string) => form.setData('wallet_id', v)} options={wallets.map((w: any) => [w.id, w.name])} />
                    <Field label="Monto" type="number" step="0.01" value={form.data.amount} onChange={(v: string) => form.setData('amount', v)} error={form.errors.amount} />
                    <Field label="Fecha y hora" type="datetime-local" value={form.data.paid_at} onChange={(v: string) => form.setData('paid_at', v)} error={form.errors.paid_at} />
                    <Field label="Nota" value={form.data.notes} onChange={(v: string) => form.setData('notes', v)} error={form.errors.notes} />
                    <button className="w-full rounded-lg bg-teal-700 px-4 py-3 font-medium text-white">{loan.kind === 'lent' ? 'Cobrar' : 'Pagar'}</button>
                </form>
                <section className="rounded-lg bg-white p-4 shadow-sm lg:col-span-2">
                    <h3 className="font-semibold">Linea de tiempo</h3>
                    <div className="mt-3 divide-y">
                        {loan.payments.length ? loan.payments.map((payment: any) => (
                            <div key={payment.id} className="flex justify-between gap-3 py-3 text-sm">
                                <div>
                                    <div className="font-medium">{loan.kind === 'lent' ? 'Cobro' : 'Pago'} registrado</div>
                                    <div className="text-gray-500">{dateTime(payment.paid_at)} {payment.notes ? `· ${payment.notes}` : ''}</div>
                                </div>
                                <strong>{money(payment.amount)}</strong>
                            </div>
                        )) : <p className="text-sm text-gray-500">Aun no hay pagos ni cobros.</p>}
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
function Field({ label, error, onChange, ...props }: any) { return <label className="block text-sm font-medium text-gray-700">{label}<input {...props} onChange={(e) => onChange(e.target.value)} className="mt-1 w-full rounded-lg border-gray-300" /><InputError message={error} className="mt-1" /></label>; }
function Select({ label, options, value, onChange }: any) { return <label className="block text-sm font-medium text-gray-700">{label}<select value={value} onChange={(e) => onChange(e.target.value)} className="mt-1 w-full rounded-lg border-gray-300">{options.map((x: any) => <option key={x[0]} value={x[0]}>{x[1]}</option>)}</select></label>; }
