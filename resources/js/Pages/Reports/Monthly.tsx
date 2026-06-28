import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { money } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/react';

export default function Monthly({ month, currency, income, expense, byCategory, debt, receivable, trend, closing }: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Reporte mensual</h2>}>
            <Head title="Reporte mensual" />
            <div className="mx-auto max-w-5xl space-y-4 px-4 py-6">
                <input type="month" defaultValue={month} onChange={(e) => router.get(route('reports.monthly'), { month: e.target.value })} className="rounded-lg border-gray-300" />
                <a href={route('reports.monthly.csv.simple', { month })} className="ml-2 inline-block rounded-lg border px-4 py-2">CSV</a>
                <a href={route('reports.monthly.print', { month })} target="_blank" className="ml-2 inline-block rounded-lg border px-4 py-2">PDF</a>
                <button onClick={() => router.post(route('reports.monthly.close'), { month }, { preserveScroll: true })} className="ml-2 rounded-lg bg-slate-800 px-4 py-2 text-white">Cerrar mes</button>
                {closing && <div className="rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">Mes cerrado: balance {money(closing.balance, currency)}</div>}
                <section className="grid gap-3 sm:grid-cols-3">
                    <Card label="Ingresos" value={income} currency={currency} />
                    <Card label="Egresos" value={expense} currency={currency} />
                    <Card label="Por pagar" value={debt} currency={currency} />
                    <Card label="Por cobrar" value={receivable} currency={currency} />
                </section>
                <section className="rounded-lg bg-white p-4 shadow-sm">
                    <h3 className="font-semibold">Tendencia</h3>
                    <div className="mt-3 space-y-3">
                        {trend.map((row: any) => {
                            const total = Math.max(Number(row.income), Number(row.expense), 1);
                            return <div key={row.month} className="text-sm"><div className="mb-1 font-medium">{row.month}</div><div className="h-2 rounded-full bg-gray-100"><div className="h-2 rounded-full bg-teal-700" style={{ width: `${(Number(row.income) / total) * 100}%` }} /></div><div className="mt-1 h-2 rounded-full bg-gray-100"><div className="h-2 rounded-full bg-rose-700" style={{ width: `${(Number(row.expense) / total) * 100}%` }} /></div></div>;
                        })}
                    </div>
                </section>
                <section className="divide-y rounded-lg bg-white shadow-sm">
                    {byCategory.map((row: any) => (
                        <div key={`${row.type}-${row.name}`} className="flex justify-between p-4"><span>{row.name}</span><strong>{money(row.amount, currency)}</strong></div>
                    ))}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
function Card({ label, value, currency }: any) { return <div className="rounded-lg bg-white p-4 shadow-sm"><div className="text-sm text-gray-500">{label}</div><div className="text-2xl font-semibold">{money(value, currency)}</div></div>; }
