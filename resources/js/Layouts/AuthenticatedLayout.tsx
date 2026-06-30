import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import { Link, usePage } from '@inertiajs/react';
import {
    AlertTriangle,
    BarChart3,
    CalendarDays,
    CreditCard,
    CircleDollarSign,
    Gauge,
    Landmark,
    LayoutGrid,
    LogOut,
    MoreHorizontal,
    Repeat,
    Settings,
    Shield,
    Target,
    UserRound,
    WalletCards,
    type LucideIcon,
} from 'lucide-react';
import { PropsWithChildren, ReactNode, useState } from 'react';

type NavItem = [string, string, LucideIcon];
type MobileGroup = { key: string; label: string; icon: LucideIcon; items: NavItem[] };

const navItems: NavItem[] = [
    ['dashboard', 'Panel', Gauge],
    ['wallets.index', 'Billeteras', WalletCards],
    ['credit-cards.index', 'Tarjetas', CreditCard],
    ['transactions.index', 'Movimientos', CreditCard],
    ['loans.index', 'Prestamos', Landmark],
    ['pending-debts.index', 'Deudas', CircleDollarSign],
    ['budgets.index', 'Presupuestos', LayoutGrid],
    ['goals.index', 'Metas', Target],
    ['recurring.index', 'Recurrentes', Repeat],
    ['calendar.index', 'Calendario', CalendarDays],
    ['reports.monthly', 'Reportes', BarChart3],
    ['alerts.index', 'Alertas', AlertTriangle],
];

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const user = usePage().props.auth.user;
    const items: NavItem[] = user.is_superuser ? [...navItems, ['admin.index', 'Admin', Shield]] : navItems;
    const [openGroup, setOpenGroup] = useState<string | null>(null);
    const mobileGroups: MobileGroup[] = [
        { key: 'inicio', label: 'Inicio', icon: Gauge, items: [['dashboard', 'Panel', Gauge]] },
        { key: 'dinero', label: 'Dinero', icon: WalletCards, items: [['wallets.index', 'Billeteras', WalletCards], ['credit-cards.index', 'Tarjetas', CreditCard], ['transactions.index', 'Movimientos', CreditCard], ['loans.index', 'Prestamos', Landmark], ['pending-debts.index', 'Deudas', CircleDollarSign]] },
        { key: 'plan', label: 'Plan', icon: Target, items: [['budgets.index', 'Presupuestos', LayoutGrid], ['goals.index', 'Metas', Target], ['recurring.index', 'Recurrentes', Repeat], ['calendar.index', 'Calendario', CalendarDays]] },
        { key: 'mas', label: 'Mas', icon: MoreHorizontal, items: [['reports.monthly', 'Reportes', BarChart3], ['alerts.index', 'Alertas', AlertTriangle], ...(user.is_superuser ? [['admin.index', 'Admin', Shield] as NavItem] : []), ['profile.edit', 'Perfil', UserRound]] },
    ];

    return (
        <div className="app-shell">
            <aside className="app-sidebar fixed inset-y-0 left-0 z-30 hidden w-72 border-r border-white/10 p-5 text-white lg:block">
                <Link href={route('dashboard')} className="flex items-center gap-3 rounded-3xl bg-white/8 p-3">
                    <ApplicationLogo className="h-12 w-12 rounded-2xl object-contain" />
                    <div>
                        <div className="text-lg font-black">Finanzas</div>
                        <div className="text-xs font-semibold text-teal-100/80">Control personal</div>
                    </div>
                </Link>

                <nav className="mt-8 space-y-1">
                    {items.map(([name, label, Icon]) => (
                        <SidebarLink key={name as string} name={name as string} label={label as string} icon={Icon} />
                    ))}
                </nav>

                <div className="absolute inset-x-5 bottom-5 rounded-3xl border border-white/10 bg-white/8 p-4">
                    <div className="flex items-center gap-3">
                        <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-400 font-black text-slate-950">
                            {user.name?.charAt(0)?.toUpperCase()}
                        </div>
                        <div className="min-w-0">
                            <div className="truncate text-sm font-bold">{user.name}</div>
                            <div className="truncate text-xs text-slate-300">{user.email}</div>
                        </div>
                    </div>
                    <div className="mt-3 grid grid-cols-2 gap-2">
                        <Link href={route('profile.edit')} className="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/10 px-3 py-2 text-xs font-bold text-white hover:bg-white/15">
                            <UserRound className="h-4 w-4" /> Perfil
                        </Link>
                        <Link href={route('logout')} method="post" as="button" className="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/10 px-3 py-2 text-xs font-bold text-white hover:bg-white/15">
                            <LogOut className="h-4 w-4" /> Salir
                        </Link>
                    </div>
                </div>
            </aside>

            <div className="app-page lg:pl-72">
                <header className="sticky top-0 z-20 border-b border-slate-200/70 bg-white/78 backdrop-blur-xl">
                    <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                        <div className="min-w-0">
                            <div className="text-xs font-bold uppercase tracking-[0.22em] text-teal-700">Finanzas App</div>
                            <div className="[&_h2]:truncate [&_h2]:text-2xl [&_h2]:font-black [&_h2]:tracking-tight [&_h2]:text-slate-950">
                                {header}
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            <Link href={route('transactions.create')} className="hidden rounded-2xl bg-slate-950 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-slate-900/20 hover:bg-slate-800 sm:inline-flex">
                                Nuevo movimiento
                            </Link>
                            <Dropdown>
                                <Dropdown.Trigger>
                                    <button className="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-600 font-black text-white shadow-lg shadow-teal-700/20">
                                        {user.name?.charAt(0)?.toUpperCase()}
                                    </button>
                                </Dropdown.Trigger>
                                <Dropdown.Content>
                                    <Dropdown.Link href={route('profile.edit')}>Perfil</Dropdown.Link>
                                    <Dropdown.Link href={route('logout')} method="post" as="button">Salir</Dropdown.Link>
                                </Dropdown.Content>
                            </Dropdown>
                        </div>
                    </div>
                </header>

                <main className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{children}</main>
            </div>

            {openGroup && (
                <div className="fixed inset-x-3 bottom-24 z-40 rounded-[1.75rem] border border-slate-200 bg-white/96 p-3 shadow-2xl shadow-slate-900/20 backdrop-blur-xl lg:hidden">
                    <div className="grid gap-2">
                        {mobileGroups.find((group) => group.key === openGroup)?.items.map(([name, label, Icon]) => (
                            <Link
                                key={name as string}
                                href={route(name as string)}
                                onClick={() => setOpenGroup(null)}
                                className={`flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-black ${
                                    route().current(`${name as string}*`) ? 'bg-slate-950 text-white' : 'bg-slate-50 text-slate-700'
                                }`}
                            >
                                <Icon className="h-5 w-5" />
                                {label}
                            </Link>
                        ))}
                    </div>
                </div>
            )}

            <nav className="app-bottom-nav fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/92 backdrop-blur-xl lg:hidden">
                <div className="grid grid-cols-4 px-2 py-2">
                    {mobileGroups.map((group) => (
                        <MobileGroupButton key={group.key} group={group} open={openGroup === group.key} onClick={() => setOpenGroup(openGroup === group.key ? null : group.key)} />
                    ))}
                </div>
            </nav>
        </div>
    );
}

function SidebarLink({ name, label, icon: Icon }: { name: string; label: string; icon: any }) {
    const active = route().current(`${name}*`);

    return (
        <Link
            href={route(name)}
            className={`flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold transition ${
                active ? 'bg-white text-slate-950 shadow-xl shadow-black/10' : 'text-slate-300 hover:bg-white/10 hover:text-white'
            }`}
        >
            <Icon className="h-5 w-5" />
            {label}
        </Link>
    );
}

function MobileGroupButton({ group, open, onClick }: { group: any; open: boolean; onClick: () => void }) {
    const Icon = group.icon;
    const active = group.items.some(([name]: any) => route().current(`${name}*`));

    return (
        <button type="button" onClick={onClick} className={`flex flex-col items-center gap-1 rounded-2xl px-2 py-2 text-[11px] font-bold ${open || active ? 'bg-slate-950 text-white' : 'text-slate-500'}`}>
            <Icon className="h-5 w-5" />
            <span className="max-w-full truncate">{group.label}</span>
        </button>
    );
}
