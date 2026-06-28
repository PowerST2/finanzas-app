import InputError from '@/Components/InputError';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { nowLocal } from '@/lib/format';
import { Head, useForm } from '@inertiajs/react';

export default function Form({ wallets }: any) {
    const form = useForm({ wallet_id: wallets[0]?.id || '', kind: 'borrowed', name: '', lender_name: '', principal_amount: '', interest_rate: '', received_at: nowLocal(), due_date: '', notes: '' });
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Registrar prestamo</h2>}>
            <Head title="Registrar prestamo" />
            <form onSubmit={(e) => { e.preventDefault(); form.post(route('loans.store')); }} className="mx-auto max-w-xl space-y-4 px-4 py-6">
                <Select label="Tipo de prestamo" value={form.data.kind} onChange={(v: string) => form.setData('kind', v)} options={[['borrowed', 'Me prestaron dinero'], ['lent', 'Yo preste dinero']]} />
                <Select label={form.data.kind === 'lent' ? 'Billetera que entrega' : 'Billetera que recibe'} value={form.data.wallet_id} onChange={(v: string) => form.setData('wallet_id', v)} options={wallets.map((w: any) => [w.id, w.name])} />
                <Field label="Nombre" value={form.data.name} onChange={(v: string) => form.setData('name', v)} error={form.errors.name} />
                <Field label={form.data.kind === 'lent' ? 'Persona que recibe' : 'Prestamista'} value={form.data.lender_name} onChange={(v: string) => form.setData('lender_name', v)} error={form.errors.lender_name} />
                <Field label={form.data.kind === 'lent' ? 'Monto prestado' : 'Monto recibido'} type="number" step="0.01" value={form.data.principal_amount} onChange={(v: string) => form.setData('principal_amount', v)} error={form.errors.principal_amount} />
                <Field label="Fecha y hora" type="datetime-local" value={form.data.received_at} onChange={(v: string) => form.setData('received_at', v)} error={form.errors.received_at} />
                <Field label="Fecha limite" type="date" value={form.data.due_date} onChange={(v: string) => form.setData('due_date', v)} error={form.errors.due_date} />
                <Field label="Notas" value={form.data.notes} onChange={(v: string) => form.setData('notes', v)} error={form.errors.notes} />
                <button className="w-full rounded-lg bg-teal-700 px-4 py-3 font-medium text-white">Guardar</button>
            </form>
        </AuthenticatedLayout>
    );
}

function Field({ label, error, onChange, ...props }: any) { return <label className="block text-sm font-medium text-gray-700">{label}<input {...props} onChange={(e) => onChange(e.target.value)} className="mt-1 w-full rounded-lg border-gray-300" /><InputError message={error} className="mt-1" /></label>; }
function Select({ label, options, value, onChange }: any) { return <label className="block text-sm font-medium text-gray-700">{label}<select value={value} onChange={(e) => onChange(e.target.value)} className="mt-1 w-full rounded-lg border-gray-300">{options.map((x: any) => <option key={x[0]} value={x[0]}>{x[1]}</option>)}</select></label>; }
