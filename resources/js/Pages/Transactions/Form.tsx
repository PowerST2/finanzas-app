import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { money, nowLocal } from '@/lib/format';
import { Head, useForm, usePage } from '@inertiajs/react';

export default function Form({ wallets, categories, currencies, defaultType, defaultDate, transaction, auditLogs = [] }: any) {
    const editing = Boolean(transaction);
    const user = usePage().props.auth.user as any;
    const form = useForm({
        wallet_id: transaction?.wallet_id || wallets[0]?.id || '',
        destination_wallet_id: transaction?.destination_wallet_id || '',
        category_id: transaction?.category_id || '',
        type: transaction?.type || defaultType,
        amount: transaction?.original_amount || transaction?.amount || '',
        currency: transaction?.original_currency || user.currency || 'PEN',
        date: transaction?.date ? transaction.date.slice(0, 16) : (defaultDate ? `${defaultDate}T12:00` : nowLocal()),
        description: transaction?.description || '',
        status: transaction?.status || 'confirmed',
        attachment: null as File | null,
    });
    const filtered = categories.filter((c: any) => c.type === (form.data.type === 'income' ? 'income' : 'expense'));
    const sourceWallet = wallets.find((w: any) => Number(w.id) === Number(form.data.wallet_id));
    const destinationWallet = wallets.find((w: any) => Number(w.id) === Number(form.data.destination_wallet_id));
    const inputCurrency = currencies.find((x: any) => x.code === form.data.currency);
    const sourceAmount = sourceWallet && inputCurrency ? Number(form.data.amount || 0) * Number(inputCurrency.exchange_rate_to_pen) / Number(sourceWallet.exchange_rate_to_pen || 1) : Number(form.data.amount || 0);
    const converted = sourceWallet && destinationWallet ? sourceAmount * Number(sourceWallet.exchange_rate_to_pen) / Number(destinationWallet.exchange_rate_to_pen || 1) : 0;
    const walletCurrency = sourceWallet?.currency || form.data.currency;
    const save = (e: React.FormEvent) => {
        e.preventDefault();
        form.transform((data) => ({ ...data, _method: editing ? 'put' : undefined }));
        form.post(editing ? route('transactions.update', transaction.id) : route('transactions.store'), { forceFormData: true });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">{editing ? 'Editar movimiento' : 'Nuevo movimiento'}</h2>}>
            <Head title={editing ? 'Editar movimiento' : 'Nuevo movimiento'} />
            <form onSubmit={save} className="mx-auto max-w-xl space-y-4 px-4 py-6">
                <Select label="Tipo" value={form.data.type} onChange={(v: string) => form.setData('type', v)} options={[['expense', 'Egreso'], ['income', 'Ingreso'], ['transfer', 'Transferencia'], ['adjustment', 'Ajuste']]} />
                <Select label="Billetera" value={form.data.wallet_id} onChange={(v: string) => form.setData('wallet_id', v)} options={wallets.map((w: any) => [w.id, w.name])} />
                {form.data.type === 'transfer' && <Select label="Destino" value={form.data.destination_wallet_id} onChange={(v: string) => form.setData('destination_wallet_id', v)} options={wallets.map((w: any) => [w.id, w.name])} />}
                {form.data.type !== 'transfer' && <Select label="Categoria" value={form.data.category_id} onChange={(v: string) => form.setData('category_id', v)} options={[['', 'Sin categoria'], ...filtered.map((c: any) => [c.id, c.name])]} />}
                <Select label="Moneda del monto" value={form.data.currency} onChange={(v: string) => form.setData('currency', v)} options={currencies.map((x: any) => [x.code, `${x.code} - ${x.name}`])} />
                <Field label="Monto" type="number" step="0.01" value={form.data.amount} onChange={(v: string) => form.setData('amount', v)} error={form.errors.amount} />
                {sourceWallet && form.data.currency !== walletCurrency && <div className="rounded-lg bg-white p-3 text-sm text-gray-600 shadow-sm">El movimiento quedara registrado en {form.data.currency}; para el saldo de {sourceWallet.name} se convertira a {money(sourceAmount, walletCurrency)}.</div>}
                {form.data.type === 'transfer' && destinationWallet && <div className="rounded-lg bg-white p-3 text-sm text-gray-600 shadow-sm">Llegara aprox. {money(converted, destinationWallet.currency)} a {destinationWallet.name}</div>}
                <Field label="Fecha y hora" type="datetime-local" value={form.data.date} onChange={(v: string) => form.setData('date', v)} error={form.errors.date} />
                <Field label="Descripcion" value={form.data.description} onChange={(v: string) => form.setData('description', v)} error={form.errors.description} />
                <Select label="Estado" value={form.data.status} onChange={(v: string) => form.setData('status', v)} options={[['confirmed', 'Confirmado'], ['pending', 'Pendiente'], ['cancelled', 'Anulado']]} />
                <label className="block text-sm font-medium text-gray-700">Comprobante<input type="file" onChange={(e) => form.setData('attachment', e.target.files?.[0] || null)} className="mt-1 w-full rounded-lg border border-gray-300 p-2" /></label>
                {transaction?.attachments?.length > 0 && <div className="rounded-lg bg-white p-3 text-sm text-gray-600">Adjuntos actuales: {transaction.attachments.length}</div>}
                <InputError message={form.errors.destination_wallet_id} />
                <button className="w-full rounded-lg bg-teal-700 px-4 py-3 font-medium text-white">Guardar</button>
                {auditLogs.length > 0 && <section className="rounded-lg bg-white p-4 shadow-sm">
                    <h3 className="font-semibold">Historial</h3>
                    <div className="mt-2 divide-y text-sm text-gray-600">
                        {auditLogs.map((log: any) => <div key={log.id} className="py-2">{log.action} · {new Date(log.created_at).toLocaleString('es-PE')}</div>)}
                    </div>
                </section>}
            </form>
        </AuthenticatedLayout>
    );
}

function Field({ label, error, onChange, ...props }: any) { return <label className="block text-sm font-medium text-gray-700">{label}<input {...props} onChange={(e) => onChange(e.target.value)} className="mt-1 w-full rounded-lg border-gray-300" /><InputError message={error} className="mt-1" /></label>; }
function Select({ label, options, value, onChange }: any) { return <label className="block text-sm font-medium text-gray-700">{label}<select value={value} onChange={(e) => onChange(e.target.value)} className="mt-1 w-full rounded-lg border-gray-300">{options.map((x: any) => Array.isArray(x) ? <option key={x[0]} value={x[0]}>{x[1]}</option> : <option key={x} value={x}>{x}</option>)}</select></label>; }
