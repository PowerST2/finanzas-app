import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Index({ users, walletTypes, currencies }: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Superusuario</h2>}>
            <Head title="Superusuario" />
            <div className="mx-auto max-w-6xl space-y-6 px-4 py-6">
                <section className="rounded-lg bg-white p-4 shadow-sm">
                    <h3 className="text-lg font-semibold">Usuarios</h3>
                    <p className="text-sm text-gray-500">Gestiona acceso, estado y permisos.</p>
                    <div className="mt-3 divide-y">{users.map((user: any) => <UserRow key={user.id} user={user} />)}</div>
                </section>

                <section className="rounded-lg bg-white p-4 shadow-sm">
                    <h3 className="text-lg font-semibold">Monedas y tipo de cambio</h3>
                    <p className="text-sm text-gray-500">Todo esta centralizado en soles: la tasa indica cuantos soles vale 1 unidad de esa moneda.</p>
                    <CurrencyCreate />
                    <div className="mt-4 divide-y">{currencies.map((currency: any) => <CurrencyRow key={currency.id} currency={currency} />)}</div>
                </section>

                <section className="rounded-lg bg-white p-4 shadow-sm">
                    <h3 className="text-lg font-semibold">Tipos de billetera</h3>
                    <p className="text-sm text-gray-500">Opciones que aparecen al crear o editar billeteras.</p>
                    <WalletTypeCreate />
                    <div className="mt-4 divide-y">{walletTypes.map((type: any) => <WalletTypeRow key={type.id} type={type} />)}</div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}

function UserRow({ user }: any) {
    const form = useForm({ name: user.name, email: user.email, is_superuser: user.is_superuser, is_active: user.is_active });
    return <form onSubmit={(e) => { e.preventDefault(); form.patch(route('admin.users.update', user.id), { preserveScroll: true }); }} className="grid gap-2 py-3 lg:grid-cols-6">
        <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="rounded-lg border-gray-300 lg:col-span-2" />
        <input value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} className="rounded-lg border-gray-300 lg:col-span-2" />
        <label className="text-sm"><input type="checkbox" checked={form.data.is_superuser} onChange={(e) => form.setData('is_superuser', e.target.checked)} /> Superusuario</label>
        <label className="text-sm"><input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} /> Activo</label>
        <button className="rounded-lg bg-slate-800 px-3 py-2 text-white lg:col-start-6">Guardar</button>
    </form>;
}

function CurrencyCreate() {
    const form = useForm({ code: '', name: '', exchange_rate_to_pen: '1.0000', is_active: true });
    return <form onSubmit={(e) => { e.preventDefault(); form.post(route('admin.currencies.store'), { preserveScroll: true, onSuccess: () => form.reset() }); }} className="mt-4 grid gap-2 lg:grid-cols-5">
        <input placeholder="Codigo: EUR" value={form.data.code} onChange={(e) => form.setData('code', e.target.value.toUpperCase())} className="rounded-lg border-gray-300" />
        <input placeholder="Nombre" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="rounded-lg border-gray-300 lg:col-span-2" />
        <input placeholder="Soles por unidad" value={form.data.exchange_rate_to_pen} onChange={(e) => form.setData('exchange_rate_to_pen', e.target.value)} className="rounded-lg border-gray-300" />
        <button className="rounded-lg bg-teal-700 px-3 py-2 text-white">Agregar moneda</button>
    </form>;
}

function CurrencyRow({ currency }: any) {
    const form = useForm({ name: currency.name, exchange_rate_to_pen: currency.exchange_rate_to_pen, is_active: currency.is_active });
    return <form onSubmit={(e) => { e.preventDefault(); form.patch(route('admin.currencies.update', currency.id), { preserveScroll: true }); }} className="grid gap-2 py-3 lg:grid-cols-5">
        <div className="font-semibold">{currency.code}</div>
        <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="rounded-lg border-gray-300" />
        <input value={form.data.exchange_rate_to_pen} onChange={(e) => form.setData('exchange_rate_to_pen', e.target.value)} className="rounded-lg border-gray-300" />
        <label className="text-sm"><input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} /> Activo</label>
        <button className="rounded-lg bg-slate-800 px-3 py-2 text-white">Guardar</button>
    </form>;
}

function WalletTypeCreate() {
    const form = useForm({ code: '', name: '', is_active: true });
    return <form onSubmit={(e) => { e.preventDefault(); form.post(route('admin.wallet-types.store'), { preserveScroll: true, onSuccess: () => form.reset() }); }} className="mt-4 grid gap-2 lg:grid-cols-4">
        <input placeholder="codigo" value={form.data.code} onChange={(e) => form.setData('code', e.target.value)} className="rounded-lg border-gray-300" />
        <input placeholder="Nombre" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="rounded-lg border-gray-300 lg:col-span-2" />
        <button className="rounded-lg bg-teal-700 px-3 py-2 text-white">Agregar tipo</button>
    </form>;
}

function WalletTypeRow({ type }: any) {
    const form = useForm({ name: type.name, is_active: type.is_active });
    return <form onSubmit={(e) => { e.preventDefault(); form.patch(route('admin.wallet-types.update', type.id), { preserveScroll: true }); }} className="grid gap-2 py-3 lg:grid-cols-4">
        <div className="font-semibold">{type.code}</div>
        <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="rounded-lg border-gray-300" />
        <label className="text-sm"><input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} /> Activo</label>
        <button className="rounded-lg bg-slate-800 px-3 py-2 text-white">Guardar</button>
    </form>;
}
