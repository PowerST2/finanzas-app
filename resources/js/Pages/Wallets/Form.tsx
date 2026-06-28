import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Form({ wallet, walletTypes, currencies }: any) {
    const editing = Boolean(wallet);
    const form = useForm({
        name: wallet?.name || '',
        type: wallet?.type || 'cash',
        currency: wallet?.currency || 'PEN',
        exchange_rate_to_pen: wallet?.exchange_rate_to_pen || '1.00',
        opening_balance: wallet?.opening_balance || '0.00',
        is_active: wallet?.is_active ?? true,
    });
    const save = (e: React.FormEvent) => {
        e.preventDefault();
        form.transform((data) => ({ ...data, _method: editing ? 'put' : undefined }));
        form.post(editing ? route('wallets.update', wallet.id) : route('wallets.store'));
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">{editing ? 'Editar billetera' : 'Nueva billetera'}</h2>}>
            <Head title={editing ? 'Editar billetera' : 'Nueva billetera'} />
            <form onSubmit={save} className="mx-auto max-w-xl space-y-4 px-4 py-6">
                <Field label="Nombre" value={form.data.name} onChange={(v: string) => form.setData('name', v)} error={form.errors.name} />
                <Select label="Tipo" value={form.data.type} onChange={(v: string) => form.setData('type', v)} options={walletTypes.map((x: any) => [x.code, x.name])} />
                <Select label="Moneda" value={form.data.currency} onChange={(v: string) => {
                    form.setData('currency', v);
                    const currency = currencies.find((x: any) => x.code === v);
                    if (currency) form.setData('exchange_rate_to_pen', currency.exchange_rate_to_pen);
                }} options={currencies.map((x: any) => [x.code, x.name])} />
                <Field label="Tasa a soles" type="number" step="0.0001" value={form.data.exchange_rate_to_pen} onChange={(v: string) => form.setData('exchange_rate_to_pen', v)} error={form.errors.exchange_rate_to_pen} />
                <Field label="Saldo inicial" type="number" step="0.01" value={form.data.opening_balance} onChange={(v: string) => form.setData('opening_balance', v)} error={form.errors.opening_balance} />
                <button className="w-full rounded-lg bg-teal-700 px-4 py-3 font-medium text-white">Guardar</button>
            </form>
        </AuthenticatedLayout>
    );
}

function Field({ label, error, onChange, ...props }: any) { return <label className="block text-sm font-medium text-gray-700">{label}<input {...props} onChange={(e) => onChange(e.target.value)} className="mt-1 w-full rounded-lg border-gray-300" /><InputError message={error} className="mt-1" /></label>; }
function Select({ label, options, value, onChange }: any) { return <label className="block text-sm font-medium text-gray-700">{label}<select value={value} onChange={(e) => onChange(e.target.value)} className="mt-1 w-full rounded-lg border-gray-300">{options.map((x: any) => Array.isArray(x) ? <option key={x[0]} value={x[0]}>{x[1]}</option> : <option key={x} value={x}>{x}</option>)}</select></label>; }
