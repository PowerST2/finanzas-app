import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { dateTime, money, nowLocal, transactionType } from '@/lib/format';
import { Head, useForm } from '@inertiajs/react';

export default function Index({ rules, wallets, categories }: any) {
    const form = useForm({ wallet_id: wallets[0]?.id || '', category_id: '', type: 'expense', frequency: 'monthly', amount: '', description: '', next_at: nowLocal() });
    const filtered = categories.filter((c: any) => c.type === form.data.type);
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Movimientos recurrentes</h2>}>
            <Head title="Movimientos recurrentes" />
            <div className="mx-auto grid max-w-5xl gap-4 px-4 py-6 lg:grid-cols-[.8fr_1.2fr]">
                <form onSubmit={(e) => { e.preventDefault(); form.post(route('recurring.store'), { onSuccess: () => form.reset('amount', 'description') }); }} className="space-y-3 rounded-lg bg-white p-4 shadow-sm">
                    <h3 className="font-semibold">Nueva regla</h3>
                    <select className="w-full rounded-lg border-gray-300" value={form.data.type} onChange={(e) => form.setData('type', e.target.value)}><option value="expense">Egreso</option><option value="income">Ingreso</option></select>
                    <select className="w-full rounded-lg border-gray-300" value={form.data.wallet_id} onChange={(e) => form.setData('wallet_id', e.target.value)}>{wallets.map((w: any) => <option key={w.id} value={w.id}>{w.name}</option>)}</select>
                    <select className="w-full rounded-lg border-gray-300" value={form.data.category_id} onChange={(e) => form.setData('category_id', e.target.value)}><option value="">Sin categoria</option>{filtered.map((c: any) => <option key={c.id} value={c.id}>{c.name}</option>)}</select>
                    <select className="w-full rounded-lg border-gray-300" value={form.data.frequency} onChange={(e) => form.setData('frequency', e.target.value)}><option value="daily">Diario</option><option value="weekly">Semanal</option><option value="monthly">Mensual</option></select>
                    <input className="w-full rounded-lg border-gray-300" placeholder="Monto" type="number" step="0.01" value={form.data.amount} onChange={(e) => form.setData('amount', e.target.value)} />
                    <input className="w-full rounded-lg border-gray-300" placeholder="Descripcion" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
                    <input className="w-full rounded-lg border-gray-300" type="datetime-local" value={form.data.next_at} onChange={(e) => form.setData('next_at', e.target.value)} />
                    <button className="w-full rounded-lg bg-teal-700 px-4 py-3 font-medium text-white">Guardar</button>
                </form>
                <section className="divide-y rounded-lg bg-white shadow-sm">
                    {rules.map((rule: any) => <div key={rule.id} className="flex justify-between gap-4 p-4">
                        <div><strong>{rule.description}</strong><div className="text-sm text-gray-500">{transactionType(rule.type)} · {rule.wallet?.name} · proximo {dateTime(rule.next_at)}</div></div>
                        <strong>{money(rule.amount)}</strong>
                    </div>)}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
