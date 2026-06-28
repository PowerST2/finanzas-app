import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { money } from '@/lib/format';
import { Head, useForm } from '@inertiajs/react';

export default function Show({ budget, categories }: any) {
    const form = useForm({ month: budget.month, total_limit: budget.total_limit || '', saving_goal: budget.saving_goal || '', items: budget.items.length ? budget.items.map((i: any) => ({ category_id: i.category_id, limit_amount: i.limit_amount })) : [] });
    const addItem = () => form.setData('items', [...form.data.items, { category_id: categories[0]?.id || '', limit_amount: '' }]);
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Presupuesto {budget.month}</h2>}>
            <Head title="Presupuesto" />
            <form onSubmit={(e) => { e.preventDefault(); form.post(route('budgets.store')); }} className="mx-auto max-w-2xl space-y-4 px-4 py-6">
                <input type="month" value={form.data.month} onChange={(e) => form.setData('month', e.target.value)} className="w-full rounded-lg border-gray-300" />
                <input placeholder="Limite total" type="number" step="0.01" value={form.data.total_limit} onChange={(e) => form.setData('total_limit', e.target.value)} className="w-full rounded-lg border-gray-300" />
                <input placeholder="Meta de ahorro" type="number" step="0.01" value={form.data.saving_goal} onChange={(e) => form.setData('saving_goal', e.target.value)} className="w-full rounded-lg border-gray-300" />
                <div className="space-y-2">
                    {form.data.items.map((item: any, index: number) => (
                        <div key={index} className="grid grid-cols-2 gap-2">
                            <select value={item.category_id} onChange={(e) => { const items = [...form.data.items]; items[index].category_id = e.target.value; form.setData('items', items); }} className="rounded-lg border-gray-300">{categories.map((c: any) => <option key={c.id} value={c.id}>{c.name}</option>)}</select>
                            <input type="number" step="0.01" value={item.limit_amount} onChange={(e) => { const items = [...form.data.items]; items[index].limit_amount = e.target.value; form.setData('items', items); }} className="rounded-lg border-gray-300" />
                        </div>
                    ))}
                </div>
                <button type="button" onClick={addItem} className="rounded-lg border px-4 py-2">Agregar categoria</button>
                <button className="w-full rounded-lg bg-teal-700 px-4 py-3 font-medium text-white">Guardar {money(form.data.total_limit)}</button>
            </form>
        </AuthenticatedLayout>
    );
}
