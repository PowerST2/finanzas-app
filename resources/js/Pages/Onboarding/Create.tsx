import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useForm, Head } from '@inertiajs/react';

export default function Create({ walletTypes, currencies }: any) {
    const form = useForm({ name: 'Dinero actual', type: 'cash', currency: 'PEN', opening_balance: '0.00' });

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Configuracion inicial</h2>}>
            <Head title="Configuracion inicial" />
            <form onSubmit={(e) => { e.preventDefault(); form.post(route('onboarding.store')); }} className="mx-auto max-w-xl space-y-4 px-4 py-6">
                <Field label="Nombre" value={form.data.name} onChange={(v: string) => form.setData('name', v)} error={form.errors.name} />
                <Select label="Tipo" value={form.data.type} onChange={(v: string) => form.setData('type', v)} options={walletTypes.map((x: any) => [x.code, x.name])} />
                <Select label="Moneda" value={form.data.currency} onChange={(v: string) => form.setData('currency', v)} options={currencies.map((x: any) => [x.code, `${x.code} - ${x.name}`])} />
                <Field label="Saldo inicial" type="number" step="0.01" value={form.data.opening_balance} onChange={(v: string) => form.setData('opening_balance', v)} error={form.errors.opening_balance} />
                <button className="w-full rounded-lg bg-teal-700 px-4 py-3 font-medium text-white">Empezar</button>
            </form>
        </AuthenticatedLayout>
    );
}

function Field({ label, error, onChange, ...props }: any) {
    return <label className="block text-sm font-medium text-gray-700">{label}<input {...props} onChange={(e) => onChange(e.target.value)} className="mt-1 w-full rounded-lg border-gray-300" /><InputError message={error} className="mt-1" /></label>;
}

function Select({ label, options, value, onChange }: any) {
    return <label className="block text-sm font-medium text-gray-700">{label}<select value={value} onChange={(e) => onChange(e.target.value)} className="mt-1 w-full rounded-lg border-gray-300">{options.map((x: any) => Array.isArray(x) ? <option key={x[0]} value={x[0]}>{x[1]}</option> : <option key={x} value={x}>{x}</option>)}</select></label>;
}
