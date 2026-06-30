import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { BarChart3, CalendarDays, CreditCard, LayoutGrid, ListChecks, Target, WalletCards } from 'lucide-react';

const steps = [
    ['Billeteras', 'Crea tu efectivo, banco, Yape, Plin y separa tarjetas de credito.', 'wallets.index', WalletCards],
    ['Movimientos', 'Registra ingresos, egresos y transferencias siempre con categoria.', 'transactions.index', ListChecks],
    ['Tarjetas', 'Controla linea, deuda, fechas de corte y pagos parciales.', 'credit-cards.index', CreditCard],
    ['Deudas', 'Anota pagos pendientes que no son prestamos y pagalos por partes.', 'pending-debts.index', LayoutGrid],
    ['Plan', 'Usa presupuestos, metas, recurrentes y calendario para anticiparte.', 'budgets.index', Target],
    ['Reportes', 'Revisa el mes, exporta CSV e imprime cuando necesites.', 'reports.monthly', BarChart3],
];

export default function Index() {
    return (
        <AuthenticatedLayout header={<h2>Guia de uso</h2>}>
            <Head title="Guia" />
            <div className="space-y-5">
                <section className="overflow-hidden rounded-[2rem] bg-slate-950 p-6 text-white shadow-2xl shadow-slate-900/20">
                    <div className="max-w-2xl">
                        <div className="inline-flex rounded-2xl bg-teal-400/15 p-3 text-teal-200"><CalendarDays className="h-6 w-6" /></div>
                        <h3 className="mt-4 text-3xl font-black">Ruta rapida para ordenar tus finanzas</h3>
                        <p className="mt-3 text-sm font-semibold text-slate-300">Empieza por registrar donde esta tu dinero real. Luego registra movimientos, controla tarjetas/deudas y usa Plan para no llegar tarde a los pagos.</p>
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {steps.map(([title, text, routeName, Icon]: any, index) => (
                        <Link key={title} href={route(routeName)} className="app-section block p-5 transition hover:-translate-y-0.5">
                            <div className="flex items-center justify-between gap-3">
                                <div className="inline-flex rounded-2xl bg-teal-50 p-3 text-teal-700"><Icon className="h-6 w-6" /></div>
                                <span className="text-sm font-black text-slate-400">{String(index + 1).padStart(2, '0')}</span>
                            </div>
                            <h4 className="mt-4 text-xl font-black text-slate-950">{title}</h4>
                            <p className="mt-2 text-sm font-semibold text-slate-500">{text}</p>
                        </Link>
                    ))}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
