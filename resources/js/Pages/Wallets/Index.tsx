import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { money, walletType } from '@/lib/format';
import { Head, Link } from '@inertiajs/react';

export default function Index({ wallets, displayCurrency }: any) {
    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Billeteras</h2>}>
            <Head title="Billeteras" />
            <div className="mx-auto max-w-4xl px-4 py-6">
                <Link href={route('wallets.create')} className="inline-block rounded-lg bg-teal-700 px-4 py-2 font-medium text-white">Nueva billetera</Link>
                <div className="mt-4 divide-y rounded-lg bg-white shadow-sm">
                    {wallets.map((wallet: any) => (
                        <div key={wallet.id} className="flex justify-between p-4">
                            <div><div className="font-medium">{wallet.name}</div><div className="text-sm text-gray-500">{walletType(wallet.type)} · moneda real {wallet.currency}</div></div>
                            <div className="text-right"><strong>{money(wallet.display_balance ?? wallet.current_balance_cache, displayCurrency)}</strong><div className="text-xs text-gray-500">Real: {money(wallet.current_balance_cache, wallet.currency)}</div><div><Link href={route('wallets.edit', wallet.id)} className="text-sm text-teal-700">Editar</Link></div></div>
                        </div>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
