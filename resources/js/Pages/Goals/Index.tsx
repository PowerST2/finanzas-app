import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { money } from '@/lib/format';
import { Head, useForm } from '@inertiajs/react';

export default function Index({ goals, wallets }: any) {
    const form = useForm({ wallet_id: '', name: '', target_amount: '', current_amount: '0', target_date: '' });
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Metas de ahorro</h2>}>
            <Head title="Metas de ahorro" />
            <div className="mx-auto grid max-w-5xl gap-4 px-4 py-6 lg:grid-cols-[.8fr_1.2fr]">
                <form onSubmit={(e) => { e.preventDefault(); form.post(route('goals.store'), { onSuccess: () => form.reset() }); }} className="space-y-3 rounded-lg bg-white p-4 shadow-sm">
                    <h3 className="font-semibold">Nueva meta</h3>
                    <input className="w-full rounded-lg border-gray-300" placeholder="Nombre" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                    <input className="w-full rounded-lg border-gray-300" placeholder="Monto objetivo" type="number" step="0.01" value={form.data.target_amount} onChange={(e) => form.setData('target_amount', e.target.value)} />
                    <input className="w-full rounded-lg border-gray-300" placeholder="Monto actual" type="number" step="0.01" value={form.data.current_amount} onChange={(e) => form.setData('current_amount', e.target.value)} />
                    <input className="w-full rounded-lg border-gray-300" type="date" value={form.data.target_date} onChange={(e) => form.setData('target_date', e.target.value)} />
                    <select className="w-full rounded-lg border-gray-300" value={form.data.wallet_id} onChange={(e) => form.setData('wallet_id', e.target.value)}>
                        <option value="">Sin billetera</option>
                        {wallets.map((w: any) => <option key={w.id} value={w.id}>{w.name}</option>)}
                    </select>
                    <button className="w-full rounded-lg bg-teal-700 px-4 py-3 font-medium text-white">Guardar</button>
                </form>
                <section className="divide-y rounded-lg bg-white shadow-sm">
                    {goals.map((goal: any) => {
                        const pct = Math.min(100, Number(goal.current_amount) / Number(goal.target_amount) * 100);
                        return <div key={goal.id} className="p-4">
                            <div className="flex justify-between"><strong>{goal.name}</strong><span>{money(goal.current_amount)} / {money(goal.target_amount)}</span></div>
                            <div className="mt-2 h-2 rounded bg-slate-100"><div className="h-2 rounded bg-teal-600" style={{ width: `${pct}%` }} /></div>
                        </div>;
                    })}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
