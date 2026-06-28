import ApplicationLogo from '@/Components/ApplicationLogo';
import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';

export default function Welcome({ auth }: PageProps) {
    return (
        <>
            <Head title="Finanzas" />
            <main className="min-h-screen bg-slate-950 text-white">
                <section className="mx-auto flex min-h-screen max-w-5xl flex-col justify-between px-6 py-8">
                    <nav className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <ApplicationLogo className="h-12 w-12" />
                            <span className="text-lg font-semibold">Finanzas</span>
                        </div>
                        <div className="flex gap-2">
                            {auth.user ? (
                                <Link href={route('dashboard')} className="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium">Entrar</Link>
                            ) : (
                                <>
                                    <Link href={route('login')} className="rounded-lg px-4 py-2 text-sm font-medium text-slate-200">Iniciar sesion</Link>
                                    <Link href={route('register')} className="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium">Crear cuenta</Link>
                                </>
                            )}
                        </div>
                    </nav>

                    <div className="grid items-end gap-10 py-12 md:grid-cols-[1.1fr_.9fr]">
                        <div>
                            <h1 className="max-w-2xl text-5xl font-semibold leading-tight md:text-7xl">Tu dinero, tus deudas y tus metas en un solo lugar.</h1>
                            <p className="mt-6 max-w-xl text-lg text-slate-300">Una PWA privada para registrar movimientos, prestamos, presupuestos y alertas sin depender de tiendas de apps.</p>
                            <Link href={auth.user ? route('dashboard') : route('login')} className="mt-8 inline-block rounded-lg bg-amber-400 px-5 py-3 font-semibold text-slate-950">Abrir finanzas</Link>
                        </div>
                        <div className="rounded-lg border border-white/10 bg-white/5 p-5">
                            <div className="text-sm text-slate-300">Saldo actual</div>
                            <div className="mt-2 text-4xl font-semibold">S/ 0.00</div>
                            <div className="mt-8 grid grid-cols-2 gap-3 text-sm">
                                <div className="rounded-md bg-teal-500/15 p-3">Ingresos</div>
                                <div className="rounded-md bg-rose-500/15 p-3">Egresos</div>
                                <div className="rounded-md bg-amber-500/15 p-3">Por pagar</div>
                                <div className="rounded-md bg-sky-500/15 p-3">Por cobrar</div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </>
    );
}
