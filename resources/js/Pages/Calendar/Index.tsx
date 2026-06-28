import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { money } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ month, items }: any) {
    const days = calendarDays(month);
    const byDate = items.reduce((acc: any, item: any) => ({ ...acc, [item.date]: [...(acc[item.date] || []), item] }), {});

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Calendario</h2>}>
            <Head title="Calendario" />
            <div className="mx-auto max-w-6xl space-y-4 px-4 py-6">
                <input type="month" defaultValue={month} onChange={(e) => router.get(route('calendar.index'), { month: e.target.value })} className="rounded-lg border-gray-300" />
                <div className="grid grid-cols-7 rounded-lg bg-white text-sm shadow-sm">
                    {['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'].map((day) => <div key={day} className="border-b p-2 font-semibold text-gray-500">{day}</div>)}
                    {days.map((day, index) => (
                        <div key={index} className={`min-h-32 border-b border-r p-2 ${day.current ? 'bg-white' : 'bg-gray-50 text-gray-400'}`}>
                            <div className="flex items-center justify-between">
                                <span className="font-medium">{day.label}</span>
                                {day.current && <Link href={route('transactions.create', { date: day.date })} className="rounded bg-gray-100 px-2 text-xs text-gray-700">+</Link>}
                            </div>
                            <div className="mt-2 space-y-1">
                                {(byDate[day.date] || []).map((item: any, i: number) => (
                                    <Link key={i} href={item.href} className="block rounded bg-teal-50 p-1 text-xs text-teal-900">
                                        <span className="block truncate">{item.title}</span>
                                        <strong>{money(item.amount)}</strong>
                                    </Link>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function calendarDays(month: string) {
    const [year, monthNumber] = month.split('-').map(Number);
    const first = new Date(year, monthNumber - 1, 1);
    const startOffset = (first.getDay() + 6) % 7;
    const start = new Date(first);
    start.setDate(first.getDate() - startOffset);
    return Array.from({ length: 42 }, (_, i) => {
        const date = new Date(start);
        date.setDate(start.getDate() + i);
        const iso = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        return { date: iso, label: date.getDate(), current: date.getMonth() === monthNumber - 1 };
    });
}
